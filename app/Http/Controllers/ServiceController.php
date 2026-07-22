<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// 1. MUST IMPORT MODELS HERE
use App\Models\FarmerLoan;
use App\Models\CropRegistration;
use App\Models\StudentLoan;
use App\Models\BusinessLoan;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\FarmerInsurance;
use App\Models\SubsidyApplication;
use App\Models\HealthInsurance;
use App\Models\MotorInsurance;
use App\Models\TravelEnquiry;
use App\Models\RealEstateEnquiry;

use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function saveData(Request $request)
    {
        Log::info("Incoming Service Save Request", [
            'type' => $request->type,
            'user_id' => $request->user_id,
            'data' => $request->data
        ]);

        $validator = Validator::make($request->all(), [
            'data.amount' => 'sometimes|numeric',
            'data.tenure' => 'sometimes|integer',
            'data.price' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        // 2. Wrap in Try-Catch to handle database errors gracefully
        try {
            $type = $request->type;
            $data = $request->data;
            $userId = $request->user_id;

            switch ($type) {
                case 'kisan_loan':
                    $record = FarmerLoan::create([
                        'user_id' => $userId,
                        'land_size' => $data['land_size'],
                        'khasra_number' => $data['khasra_number'],
                        'amount' => $data['amount'],
                        'details' => $data,
                    ]);
                    break;

                case 'crop_reg':
                    $record = CropRegistration::create([
                        'user_id' => $userId,
                        'crop_name' => $data['crop_name'],
                        'price' => $data['price'],
                        'image_base64' => $data['image'] ?? null,
                        'details' => $data,
                    ]);
                    break;

                case 'edu_loan':
                    $record = StudentLoan::create([
                        'user_id' => $userId,
                        'college_name' => $data['college_name'],
                        'course_name' => $data['course_name'],
                        'amount' => $data['amount'],
                        'details' => $data,
                    ]);
                    break;

                case 'biz_loan':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => $data['amount'],
                        'purpose' => $data['purpose'],
                        'tenure' => $data['tenure'],
                        'details' => $data,
                    ]);
                    break;

                case 'gst_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'GST Registration',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'msme_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'MSME Registration',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'shop_act':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Shop Act License',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'company_firm':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Company Firm Registration',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'marketing_support':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Marketing Support',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'jan_dhan_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Jan Dhan Account',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'online_banking_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Online Banking',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'upi_payment_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'UPI Payments',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'dbt_reg':
                    $record = BusinessLoan::create([
                        'user_id' => $userId,
                        'amount' => 0,
                        'purpose' => 'Direct Benefit Transfer',
                        'tenure' => 0,
                        'details' => $data,
                    ]);
                    break;

                case 'farmer_insurance':
                    $record = FarmerInsurance::create([
                        'user_id'          => $userId,
                        'farmer_name'      => $data['farmer_name'] ?? null,
                        'father_name'      => $data['father_name'] ?? null,
                        'mobile'           => $data['mobile'] ?? null,
                        'aadhaar'          => $data['aadhaar'] ?? null,
                        'village'          => $data['village'] ?? null,
                        'tehsil'           => $data['tehsil'] ?? null,
                        'district'         => $data['district'] ?? null,
                        'state'            => $data['state'] ?? null,
                        'pincode'          => $data['pincode'] ?? null,
                        'land_size'        => $data['land_size'] ?? null,
                        'khasra_number'    => $data['khasra_number'] ?? null,
                        'survey_number'    => $data['survey_number'] ?? null,
                        'crop_name'        => $data['crop_name'] ?? null,
                        'season'           => $data['season'] ?? null,
                        'sowing_date'      => $data['sowing_date'] ?? null,
                        'expected_harvest' => $data['expected_harvest'] ?? null,
                        'sum_insured'      => $data['sum_insured'] ?? null,
                        'premium_amount'   => $data['premium_amount'] ?? null,
                        'bank_name'        => $data['bank_name'] ?? null,
                        'account_number'   => $data['account_number'] ?? null,
                        'ifsc'             => $data['ifsc'] ?? null,
                        'status'           => 'Pending',
                        'details'          => $data,
                    ]);
                    break;

                case 'health_insurance':
                    $record = HealthInsurance::create([
                        'user_id'              => $userId,
                        'applicant_name'       => $data['applicant_name'] ?? null,
                        'mobile'               => $data['mobile'] ?? null,
                        'email'                => $data['email'] ?? null,
                        'aadhaar'              => $data['aadhaar'] ?? null,
                        'pan'                  => $data['pan'] ?? null,
                        'dob'                  => $data['dob'] ?? null,
                        'gender'               => $data['gender'] ?? null,
                        'age'                  => $data['age'] ?? null,
                        'address'              => $data['address'] ?? null,
                        'city'                 => $data['city'] ?? null,
                        'state'                => $data['state'] ?? null,
                        'pincode'              => $data['pincode'] ?? null,
                        'plan_type'            => $data['plan_type'] ?? null,
                        'sum_insured'          => $data['sum_insured'] ?? null,
                        'premium_amount'       => $data['premium_amount'] ?? null,
                        'members_covered'      => $data['members_covered'] ?? null,
                        'insurer_name'         => $data['insurer_name'] ?? null,
                        'policy_term'          => $data['policy_term'] ?? null,
                        'pre_existing_disease' => isset($data['pre_existing_disease']) ? (bool)$data['pre_existing_disease'] : false,
                        'disease_details'      => $data['disease_details'] ?? null,
                        'nominee_name'         => $data['nominee_name'] ?? null,
                        'nominee_relation'     => $data['nominee_relation'] ?? null,
                        'nominee_dob'          => $data['nominee_dob'] ?? null,
                        'status'               => 'Pending',
                        'details'              => $data,
                    ]);
                    break;

                case 'motor_insurance':
                    $record = MotorInsurance::create([
                        'user_id'               => $userId,
                        'applicant_name'        => $data['applicant_name'] ?? null,
                        'mobile'                => $data['mobile'] ?? null,
                        'email'                 => $data['email'] ?? null,
                        'aadhaar'               => $data['aadhaar'] ?? null,
                        'pan'                   => $data['pan'] ?? null,
                        'address'               => $data['address'] ?? null,
                        'city'                  => $data['city'] ?? null,
                        'state'                 => $data['state'] ?? null,
                        'pincode'               => $data['pincode'] ?? null,
                        'vehicle_type'          => $data['vehicle_type'] ?? null,
                        'vehicle_make'          => $data['vehicle_make'] ?? null,
                        'vehicle_model'         => $data['vehicle_model'] ?? null,
                        'vehicle_year'          => $data['vehicle_year'] ?? null,
                        'registration_number'   => $data['registration_number'] ?? null,
                        'engine_number'         => $data['engine_number'] ?? null,
                        'chassis_number'        => $data['chassis_number'] ?? null,
                        'vehicle_value'         => $data['vehicle_value'] ?? null,
                        'plan_type'             => $data['plan_type'] ?? null,
                        'insurer_name'          => $data['insurer_name'] ?? null,
                        'premium_amount'        => $data['premium_amount'] ?? null,
                        'policy_term'           => $data['policy_term'] ?? null,
                        'has_previous_policy'   => isset($data['has_previous_policy']) ? (bool)$data['has_previous_policy'] : false,
                        'previous_policy_number'=> $data['previous_policy_number'] ?? null,
                        'previous_insurer'      => $data['previous_insurer'] ?? null,
                        'claim_history'         => $data['claim_history'] ?? null,
                        'nominee_name'          => $data['nominee_name'] ?? null,
                        'nominee_relation'      => $data['nominee_relation'] ?? null,
                        'status'                => 'Pending',
                        'details'               => $data,
                    ]);
                    break;

                case 'subsidy_application':
                    $record = SubsidyApplication::create([
                        'user_id'        => $userId,
                        'applicant_name' => $data['applicant_name'] ?? null,
                        'father_name'    => $data['father_name'] ?? null,
                        'mobile'         => $data['mobile'] ?? null,
                        'aadhaar'        => $data['aadhaar'] ?? null,
                        'village'        => $data['village'] ?? null,
                        'tehsil'         => $data['tehsil'] ?? null,
                        'district'       => $data['district'] ?? null,
                        'state'          => $data['state'] ?? null,
                        'pincode'        => $data['pincode'] ?? null,
                        'subsidy_type'   => $data['subsidy_type'] ?? null,
                        'scheme_name'    => $data['scheme_name'] ?? null,
                        'purpose'        => $data['purpose'] ?? null,
                        'land_size'      => $data['land_size'] ?? null,
                        'khasra_number'  => $data['khasra_number'] ?? null,
                        'bank_name'      => $data['bank_name'] ?? null,
                        'account_number' => $data['account_number'] ?? null,
                        'ifsc'           => $data['ifsc'] ?? null,
                        'status'         => 'Pending',
                        'details'        => $data,
                    ]);
                    break;

                case 'job_post':
                    $record = JobPosting::create([
                        'user_id' => $userId,
                        'job_title' => $data['job_title'],
                        'description' => $data['description'],
                        'salary_range' => $data['salary_range'],
                        'details' => $data,
                    ]);
                    break;

                case 'job_apply':
                    // Check if already applied
                    $exists = JobApplication::where('user_id', $userId)->where('job_id', $data['job_id'])->first();
                    if ($exists) {
                        return response()->json(['status' => 'error', 'message' => 'Already applied for this job'], 400);
                    }

                    $record = JobApplication::create([
                        'user_id' => $userId,
                        'job_id' => $data['job_id'],
                        'status' => 'Pending',
                        'details' => $data,
                    ]);
                    break;

                case 'student_admission':
                case 'student_scholarship':
                case 'student_internship':
                case 'student_job_application':
                    $record = StudentLoan::create([
                        'user_id' => $userId,
                        'college_name' => $data['college'] ?? '',
                        'course_name' => $data['course'] ?? '',
                        'amount' => 0,
                        'details' => array_merge($data, ['form_type' => $type]),
                    ]);
                    break;

                case 'travel_enquiry':
                    $record = TravelEnquiry::create([
                        'user_id' => $userId,
                        'destination' => $data['destination'] ?? '',
                        'travel_date' => $data['travel_date'] ?? null,
                        'passengers' => $data['passengers'] ?? 1,
                        'details' => $data,
                        'status' => 'Pending'
                    ]);
                    break;

                case 'real_estate_enquiry':
                    $record = RealEstateEnquiry::create([
                        'user_id' => $userId,
                        'action' => $data['action'] ?? '',
                        'property_type' => $data['property_type'] ?? '',
                        'location' => $data['location'] ?? '',
                        'budget' => $data['budget'] ?? null,
                        'details' => $data,
                        'status' => 'Pending'
                    ]);
                    break;

                default:
                    return response()->json(['error' => 'Invalid service type'], 400);
            }

            // 3. Always return a 201 Created status for successful saves
            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully',
                'data' => $record
            ], 201);

        } catch (\Exception $e) {
            // 4. Log the error for debugging
            Log::error("Service Save Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}