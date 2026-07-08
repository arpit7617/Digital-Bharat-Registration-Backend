<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{
    /**
     * Fetch all types of leads (Loans, Job Posts, Applications, Crops)
     */
    public function getAllLeads(Request $request)
    {
        try {
            $bankUserId = $request->query('bank_user_id');
            $bankCity = null;
            if ($bankUserId) {
                $bankCity = DB::table('registrations')->where('id', $bankUserId)->value('city');
            }

            // --- LOAN QUERIES ---
            $farmers = DB::table('farmer_loans')
                ->join('registrations as farmer', 'farmer_loans.user_id', '=', 'farmer.id');

            $students = DB::table('student_loans')
                ->join('registrations as student', 'student_loans.user_id', '=', 'student.id');

            $business = DB::table('business_loans')
                ->join('registrations as biz', 'business_loans.user_id', '=', 'biz.id');

            // Apply city-based filter if bank employee has a city set
            if ($bankCity) {
                $lcCity = strtolower(trim($bankCity));
                $farmers->whereRaw('LOWER(farmer.city) = ?', [$lcCity]);
                $students->whereRaw('LOWER(student.city) = ?', [$lcCity]);
                $business->whereRaw('LOWER(biz.city) = ?', [$lcCity]);
            }

            $farmers = $farmers->select(
                'farmer_loans.id',
                'farmer.name',
                'farmer.mobile',
                'farmer.email',
                'farmer.city',
                'farmer.state',
                'farmer_loans.amount',
                'farmer_loans.status',
                'farmer_loans.claimed_by',
                'farmer_loans.created_at',
                DB::raw("'Farmer Loan' as loan_type"),
                DB::raw("'farmer_loans' as table_name"),
                DB::raw("JSON_OBJECT('Land Size', farmer_loans.land_size, 'Khasra Number', farmer_loans.khasra_number) as extra_data"),
                'farmer_loans.details'
            );

            $students = $students->select(
                'student_loans.id',
                'student.name',
                'student.mobile',
                'student.email',
                'student.city',
                'student.state',
                'student_loans.amount',
                'student_loans.status',
                'student_loans.claimed_by',
                'student_loans.created_at',
                DB::raw("'Education Loan' as loan_type"),
                DB::raw("'student_loans' as table_name"),
                DB::raw("JSON_OBJECT('College Name', student_loans.college_name, 'Course Name', student_loans.course_name) as extra_data"),
                'student_loans.details'
            );

            $business = $business->select(
                'business_loans.id',
                'biz.name',
                'biz.mobile',
                'biz.email',
                'biz.city',
                'biz.state',
                'business_loans.amount',
                'business_loans.status',
                'business_loans.claimed_by',
                'business_loans.created_at',
                DB::raw("CASE 
                    WHEN business_loans.purpose = 'GST Registration' THEN 'GST Registration'
                    WHEN business_loans.purpose = 'MSME Registration' THEN 'MSME Registration'
                    WHEN business_loans.purpose = 'Shop Act License' THEN 'Shop Act License'
                    WHEN business_loans.purpose = 'Company Firm Registration' THEN 'Company Firm Registration'
                    WHEN business_loans.purpose = 'Marketing Support' THEN 'Marketing Support'
                    WHEN business_loans.purpose = 'Jan Dhan Account' THEN 'Jan Dhan Account'
                    WHEN business_loans.purpose = 'Online Banking' THEN 'Online Banking'
                    WHEN business_loans.purpose = 'UPI Payments' THEN 'UPI Payments'
                    WHEN business_loans.purpose = 'Direct Benefit Transfer' THEN 'Direct Benefit Transfer'
                    ELSE 'Business Loan' 
                END as loan_type"),
                DB::raw("'business_loans' as table_name"),
                DB::raw("JSON_OBJECT('Purpose', business_loans.purpose, 'Tenure (Months)', business_loans.tenure) as extra_data"),
                'business_loans.details'
            );

            $type = $request->query('type');
            $table = $request->query('table');

            if ($table === 'farmer_loans' || $type === 'Farmer Loan') {
                $leads = $farmers->orderBy('created_at', 'desc')->get();
            } elseif ($table === 'student_loans' || $type === 'Education Loan') {
                $leads = $students->orderBy('created_at', 'desc')->get();
            } elseif ($table === 'business_loans' || $type === 'Business Loan') {
                $leads = $business->orderBy('created_at', 'desc')->get();
            } else {
                // Combine All Loans
                $leads = $farmers->unionAll($students)
                    ->unionAll($business)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            return response()->json($leads, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required',
            'bank_user_id' => 'nullable'
        ]);

        $tableName = $request->table ?? 'farmer_loans';

        $updateData = [
            'status' => $request->status,
            'claimed_by' => $request->bank_user_id,
            'updated_at' => now(),
        ];

        if ($request->has('details')) {
            $updateData['details'] = is_array($request->details) ? json_encode($request->details) : $request->details;
        }

        DB::table($tableName)
            ->where('id', $request->id)
            ->update($updateData);

        return response()->json(['success' => true]);
    }

    public function getUserLoans(Request $request)
    {
        $userId = $request->query('user_id');
        $table = $request->query('table', 'farmer_loans');

        if (!$userId) {
            return response()->json(['message' => 'user_id is required'], 400);
        }

        $allowedTables = [
            'farmer_loans' => [
                'loan_type' => "'Farmer Loan'",
                'extra' => "JSON_OBJECT('Land Size', farmer_loans.land_size, 'Khasra Number', farmer_loans.khasra_number)",
            ],
            'student_loans' => [
                'loan_type' => "'Education Loan'",
                'extra' => "JSON_OBJECT('College Name', student_loans.college_name, 'Course Name', student_loans.course_name)",
            ],
            'business_loans' => [
                'loan_type' => "CASE 
                    WHEN business_loans.purpose = 'GST Registration' THEN 'GST Registration'
                    WHEN business_loans.purpose = 'MSME Registration' THEN 'MSME Registration'
                    WHEN business_loans.purpose = 'Shop Act License' THEN 'Shop Act License'
                    WHEN business_loans.purpose = 'Company Firm Registration' THEN 'Company Firm Registration'
                    WHEN business_loans.purpose = 'Marketing Support' THEN 'Marketing Support'
                    WHEN business_loans.purpose = 'Jan Dhan Account' THEN 'Jan Dhan Account'
                    WHEN business_loans.purpose = 'Online Banking' THEN 'Online Banking'
                    WHEN business_loans.purpose = 'UPI Payments' THEN 'UPI Payments'
                    WHEN business_loans.purpose = 'Direct Benefit Transfer' THEN 'Direct Benefit Transfer'
                    ELSE 'Business Loan' 
                END",
                'extra' => "JSON_OBJECT('Purpose', business_loans.purpose, 'Tenure (Months)', business_loans.tenure)",
            ],
        ];

        if (!array_key_exists($table, $allowedTables)) {
            return response()->json(['message' => 'Invalid table'], 400);
        }

        $meta = $allowedTables[$table];
        $loanType = $meta['loan_type'];
        $extraSql = $meta['extra'];

        $leads = DB::table($table)
            ->join('registrations', "{$table}.user_id", '=', 'registrations.id')
            ->select(
                "{$table}.id",
                'registrations.name',
                'registrations.mobile',
                'registrations.email',
                'registrations.city',
                'registrations.state',
                "{$table}.amount",
                "{$table}.status",
                "{$table}.created_at",
                DB::raw("{$loanType} as loan_type"),
                DB::raw("'{$table}' as table_name"),
                DB::raw("{$extraSql} as extra_data"),
                "{$table}.details"
            )
            ->where("{$table}.user_id", $userId)
            ->orderBy("{$table}.created_at", 'desc')
            ->get();

        return response()->json($leads, 200);
    }

    public function getMyAcceptedLeads(Request $request)
    {
        $bankUserId = $request->query('bank_user_id');

        $farmers = DB::table('farmer_loans')
            ->join('registrations', 'farmer_loans.user_id', '=', 'registrations.id')
            ->select('farmer_loans.id', 'registrations.name', 'registrations.mobile', 'registrations.email', 'registrations.city', 'registrations.state', 'farmer_loans.amount', 'farmer_loans.status', 'farmer_loans.created_at', DB::raw("'Farmer Loan' as loan_type"), DB::raw("'farmer_loans' as table_name"), DB::raw("JSON_OBJECT('Land Size', farmer_loans.land_size, 'Khasra Number', farmer_loans.khasra_number) as extra_data"), 'farmer_loans.details')
            ->where('farmer_loans.claimed_by', $bankUserId)
            ->where('farmer_loans.status', 'Approved');

        $students = DB::table('student_loans')
            ->join('registrations', 'student_loans.user_id', '=', 'registrations.id')
            ->select('student_loans.id', 'registrations.name', 'registrations.mobile', 'registrations.email', 'registrations.city', 'registrations.state', 'student_loans.amount', 'student_loans.status', 'student_loans.created_at', DB::raw("'Education Loan' as loan_type"), DB::raw("'student_loans' as table_name"), DB::raw("JSON_OBJECT('College Name', student_loans.college_name, 'Course Name', student_loans.course_name) as extra_data"), 'student_loans.details')
            ->where('student_loans.claimed_by', $bankUserId)
            ->where('student_loans.status', 'Approved');

        $business = DB::table('business_loans')
            ->join('registrations', 'business_loans.user_id', '=', 'registrations.id')
            ->select('business_loans.id', 'registrations.name', 'registrations.mobile', 'registrations.email', 'registrations.city', 'registrations.state', 'business_loans.amount', 'business_loans.status', 'business_loans.created_at', DB::raw("CASE 
                WHEN business_loans.purpose = 'GST Registration' THEN 'GST Registration'
                WHEN business_loans.purpose = 'MSME Registration' THEN 'MSME Registration'
                WHEN business_loans.purpose = 'Shop Act License' THEN 'Shop Act License'
                WHEN business_loans.purpose = 'Company Firm Registration' THEN 'Company Firm Registration'
                WHEN business_loans.purpose = 'Marketing Support' THEN 'Marketing Support'
                WHEN business_loans.purpose = 'Jan Dhan Account' THEN 'Jan Dhan Account'
                WHEN business_loans.purpose = 'Online Banking' THEN 'Online Banking'
                WHEN business_loans.purpose = 'UPI Payments' THEN 'UPI Payments'
                WHEN business_loans.purpose = 'Direct Benefit Transfer' THEN 'Direct Benefit Transfer'
                ELSE 'Business Loan' 
            END as loan_type"), DB::raw("'business_loans' as table_name"), DB::raw("JSON_OBJECT('Purpose', business_loans.purpose, 'Tenure (Months)', business_loans.tenure) as extra_data"), 'business_loans.details')
            ->where('business_loans.claimed_by', $bankUserId)
            ->where('business_loans.status', 'Approved');

        return $farmers->unionAll($students)
            ->unionAll($business)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function deleteLead(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $tableName = $request->table ?? 'farmer_loans';

        DB::table($tableName)->where('id', $request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function getDashboardStats(Request $request)
    {
        $bankUserId = $request->query('bank_user_id');
        $bankCity = null;
        if ($bankUserId) {
            $bankCity = DB::table('registrations')->where('id', $bankUserId)->value('city');
        }

        $farmerQuery = DB::table('farmer_loans')
            ->join('registrations as farmer', 'farmer_loans.user_id', '=', 'farmer.id');
        $studentQuery = DB::table('student_loans')
            ->join('registrations as student', 'student_loans.user_id', '=', 'student.id');
        $businessQuery = DB::table('business_loans')
            ->join('registrations as biz', 'business_loans.user_id', '=', 'biz.id');
        $jobPostQuery = DB::table('job_postings')
            ->join('registrations as company', 'job_postings.user_id', '=', 'company.id');
        $jobAppQuery = DB::table('job_applications')
            ->join('registrations as applicant', 'job_applications.user_id', '=', 'applicant.id');
        $cropQuery = DB::table('crop_registrations')
            ->join('registrations as farmer', 'crop_registrations.user_id', '=', 'farmer.id');

        if ($bankCity) {
            $lcCity = strtolower(trim($bankCity));
            $farmerQuery->whereRaw('LOWER(farmer.city) = ?', [$lcCity]);
            $studentQuery->whereRaw('LOWER(student.city) = ?', [$lcCity]);
            $businessQuery->whereRaw('LOWER(biz.city) = ?', [$lcCity]);
            $jobPostQuery->whereRaw('LOWER(company.city) = ?', [$lcCity]);
            $jobAppQuery->whereRaw('LOWER(applicant.city) = ?', [$lcCity]);
            $cropQuery->whereRaw('LOWER(farmer.city) = ?', [$lcCity]);
        }

        $farmerCount = $farmerQuery->count();
        $studentCount = $studentQuery->count();
        $businessCount = $businessQuery->count();
        $jobPostCount = $jobPostQuery->count();
        $jobAppCount = $jobAppQuery->count();
        $cropCount = $cropQuery->count();

        $approvedCount = (clone $farmerQuery)->where('farmer_loans.status', 'Approved')->count() +
                         (clone $studentQuery)->where('student_loans.status', 'Approved')->count() +
                         (clone $businessQuery)->where('business_loans.status', 'Approved')->count();

        return response()->json([
            'users' => $farmerCount + $studentCount + $businessCount + $jobPostCount + $jobAppCount + $cropCount,
            'approved' => $approvedCount,
            'registrations' => $farmerCount + $studentCount + $businessCount + $jobPostCount + $jobAppCount + $cropCount,
            'services' => [
                'Farmer Loan' => $farmerCount,
                'Education Loan' => $studentCount,
                'Business Loan' => $businessCount,
                'Job Posting' => $jobPostCount,
                'Job Application' => $jobAppCount,
                'Crop Registration' => $cropCount
            ]
        ], 200);
    }

    public function getJobs()
    {
        return DB::table('job_postings')
            ->join('registrations', 'job_postings.user_id', '=', 'registrations.id')
            ->select('job_postings.*', 'registrations.name as company_name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBusinessJobs(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) return response()->json([], 400);

        return DB::table('job_postings')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBusinessApplicants(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) return response()->json([], 400);

        return DB::table('job_applications')
            ->join('registrations', 'job_applications.user_id', '=', 'registrations.id')
            ->join('job_postings', 'job_applications.job_id', '=', 'job_postings.id')
            ->where('job_postings.user_id', $userId)
            ->select(
                'job_applications.id as application_id',
                'job_applications.status',
                'job_applications.created_at',
                'registrations.name as applicant_name',
                'registrations.email as applicant_email',
                'registrations.mobile as applicant_mobile',
                'job_postings.job_title',
                'job_applications.details'
            )
            ->orderBy('job_applications.created_at', 'desc')
            ->get();
    }

    public function getMyJobApplications(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) return response()->json([], 400);

        return DB::table('job_applications')
            ->join('job_postings', 'job_applications.job_id', '=', 'job_postings.id')
            ->join('registrations', 'job_postings.user_id', '=', 'registrations.id')
            ->where('job_applications.user_id', $userId)
            ->select(
                'job_applications.id as application_id',
                'job_applications.status',
                'job_applications.created_at',
                'job_applications.details',
                'job_postings.job_title',
                'job_postings.salary_range',
                'job_postings.description as job_description',
                'registrations.name as company_name'
            )
            ->orderBy('job_applications.created_at', 'desc')
            ->get();
    }

    public function getMarketCrops()
    {
        return DB::table('crop_registrations')
            ->join('registrations', 'crop_registrations.user_id', '=', 'registrations.id')
            ->select('crop_registrations.*', 'registrations.name as farmer_name', 'registrations.mobile as farmer_mobile', 'registrations.city as farmer_city')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFarmerInsuranceApplications(Request $request)
    {
        try {
            $apps = DB::table('farmer_insurance_applications')
                ->leftJoin('registrations', 'farmer_insurance_applications.user_id', '=', 'registrations.id')
                ->select(
                    'farmer_insurance_applications.*',
                    'registrations.name as reg_name',
                    'registrations.mobile as reg_mobile',
                    'registrations.city as reg_city',
                    'registrations.email as reg_email'
                )
                ->orderBy('farmer_insurance_applications.created_at', 'desc')
                ->get()
                ->map(function ($row) {
                    $details = $row->details ? json_decode($row->details, true) : [];
                    return [
                        'id'               => $row->id,
                        'user_id'          => $row->user_id,
                        'farmer_name'      => $row->farmer_name ?: $row->reg_name,
                        'mobile'           => $row->mobile ?: $row->reg_mobile,
                        'email'            => $row->reg_email,
                        'village'          => $row->village,
                        'district'         => $row->district,
                        'state'            => $row->state,
                        'crop_name'        => $row->crop_name,
                        'season'           => $row->season,
                        'land_size'        => $row->land_size,
                        'sum_insured'      => $row->sum_insured,
                        'premium_amount'   => $row->premium_amount,
                        'bank_name'        => $row->bank_name,
                        'status'           => $row->status,
                        'created_at'       => $row->created_at,
                        'details'          => $details,
                    ];
                });

            return response()->json(['status' => 'success', 'data' => $apps]);
        } catch (\Exception $e) {
            Log::error('getFarmerInsuranceApplications error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getSubsidyApplications(Request $request)
    {
        try {
            $apps = DB::table('subsidy_applications')
                ->leftJoin('registrations', 'subsidy_applications.user_id', '=', 'registrations.id')
                ->select(
                    'subsidy_applications.*',
                    'registrations.name as reg_name',
                    'registrations.mobile as reg_mobile',
                    'registrations.city as reg_city',
                    'registrations.email as reg_email'
                )
                ->orderBy('subsidy_applications.created_at', 'desc')
                ->get()
                ->map(function ($row) {
                    $details = $row->details ? json_decode($row->details, true) : [];
                    return [
                        'id'             => $row->id,
                        'user_id'        => $row->user_id,
                        'applicant_name' => $row->applicant_name ?: $row->reg_name,
                        'mobile'         => $row->mobile ?: $row->reg_mobile,
                        'email'          => $row->reg_email,
                        'district'       => $row->district,
                        'state'          => $row->state,
                        'subsidy_type'   => $row->subsidy_type,
                        'scheme_name'    => $row->scheme_name,
                        'purpose'        => $row->purpose,
                        'land_size'      => $row->land_size,
                        'bank_name'      => $row->bank_name,
                        'status'         => $row->status,
                        'created_at'     => $row->created_at,
                        'details'        => $details,
                    ];
                });

            return response()->json(['status' => 'success', 'data' => $apps]);
        } catch (\Exception $e) {
            Log::error('getSubsidyApplications error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}