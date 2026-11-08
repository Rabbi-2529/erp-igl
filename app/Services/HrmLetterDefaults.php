<?php

namespace App\Services;

/**
 * HRM Letters: the standard template of each letter type (English). They are copied into hrm_letter_templates
 * the first time the templates page opens; after that each company edits its own copy.
 */
class HrmLetterDefaults
{
    const TO = '<p>To<br><b>{{employee_name}}</b><br>{{designation}}, {{department}}<br>Employee ID: {{employee_id}}</p>';
    const CONCERN = '<p style="text-align: center"><b><u>TO WHOM IT MAY CONCERN</u></b></p>';

    /** code => [subject, body] */
    public static function all()
    {
        $to = self::TO;
        $concern = self::CONCERN;
        $dear = '<p>Dear {{employee_name}},</p>';
        $sincerely = '<p>Sincerely,</p>';
        return [
            'appointment' => ['Letter of Appointment', $to . $dear
                . '<p>With reference to your application and the interview that followed, we are pleased to appoint you as <b>{{designation}}</b> in the <b>{{department}}</b> department of {{company_name}}, {{branch_name}}, with effect from <b>{{effective_date}}</b>, on the following terms and conditions:</p>'
                . '<ol>'
                . '<li><b>Salary:</b> your gross monthly salary will be BDT {{salary}} ({{salary_in_words}}).</li>'
                . '<li><b>Probation:</b> you will be on probation for six (6) months from the date of joining. On satisfactory completion, your employment will be confirmed in writing.</li>'
                . '<li><b>Working hours and leave:</b> you will follow the office hours, attendance and leave rules of the company in force from time to time.</li>'
                . '<li><b>Notice:</b> during probation either party may end the employment with one (1) month\'s notice; after confirmation, with two (2) months\' notice or salary in lieu of notice.</li>'
                . '<li><b>Confidentiality:</b> you will keep all business information of the company confidential, during and after your employment.</li>'
                . '<li><b>Conduct:</b> you will abide by the rules, policies and code of conduct of the company.</li>'
                . '</ol>'
                . '<p>Please sign and return the duplicate copy of this letter as your acceptance of these terms.</p>'
                . '<p>We welcome you to {{company_name}} and wish you a successful career with us.</p>' . $sincerely],

            'offer' => ['Offer of Employment', $to . $dear
                . '<p>We are delighted to offer you the position of <b>{{designation}}</b> in the <b>{{department}}</b> department of {{company_name}}. We were impressed by your experience and believe you will be a valuable member of our team.</p>'
                . '<p>The main terms of this offer are:</p>'
                . '<table><tbody>'
                . '<tr><td><b>Position</b></td><td>{{designation}}</td></tr>'
                . '<tr><td><b>Department</b></td><td>{{department}}</td></tr>'
                . '<tr><td><b>Place of work</b></td><td>{{branch_name}}, {{company_address}}</td></tr>'
                . '<tr><td><b>Joining date</b></td><td>{{effective_date}}</td></tr>'
                . '<tr><td><b>Gross monthly salary</b></td><td>BDT {{salary}} ({{salary_in_words}})</td></tr>'
                . '<tr><td><b>Probation</b></td><td>Six (6) months</td></tr>'
                . '</tbody></table>'
                . '<p>Your appointment letter with the full terms and conditions will be issued on joining. This offer is subject to verification of your documents and references.</p>'
                . '<p>Please confirm your acceptance by signing a copy of this letter and returning it to us on or before <b>{{reply_by}}</b>. If we do not hear from you by then, this offer will lapse.</p>'
                . '<p>We look forward to welcoming you.</p>' . $sincerely],

            'joining' => ['Joining Letter', $to . $dear
                . '<p>This is to confirm that you have joined {{company_name}} as <b>{{designation}}</b> in the <b>{{department}}</b> department on <b>{{effective_date}}</b>, as per your appointment letter.</p>'
                . '<p>Your Employee ID is <b>{{employee_id}}</b>. Please use it for attendance, leave and all official communication. Your length of service will be counted from the date of joining above.</p>'
                . '<p>You will report to your department head, who will brief you on your duties. Please complete the joining formalities with the HR department, including submission of your documents and photographs, within seven (7) days.</p>'
                . '<p>We welcome you aboard and wish you every success.</p>' . $sincerely],

            'employment_certificate' => ['Employment Certificate', $concern
                . '<p>This is to certify that <b>{{employee_name}}</b>, Employee ID {{employee_id}}, has been working with {{company_name}} since <b>{{joining_date}}</b>. At present, {{employee_name}} holds the position of <b>{{designation}}</b> in the <b>{{department}}</b> department at our {{branch_name}} office.</p>'
                . '<p>This certificate is issued at the request of the employee for official purposes and does not create any financial obligation on the part of the company.</p>'],

            'experience' => ['Experience Certificate', $concern
                . '<p>This is to certify that <b>{{employee_name}}</b>, Employee ID {{employee_id}}, worked with {{company_name}} from <b>{{joining_date}}</b> to <b>{{last_working_day}}</b>. During this period, {{employee_name}} served as <b>{{designation}}</b> in the <b>{{department}}</b> department.</p>'
                . '<p>Throughout the employment, {{employee_name}} was found sincere, hardworking and dependable, and maintained a good attitude towards work and colleagues.</p>'
                . '<p>We wish {{employee_name}} every success in all future endeavours.</p>'],

            'salary_certificate' => ['Salary Certificate', $concern
                . '<p>This is to certify that <b>{{employee_name}}</b>, Employee ID {{employee_id}}, is an employee of {{company_name}}, working as <b>{{designation}}</b> in the <b>{{department}}</b> department since <b>{{joining_date}}</b>.</p>'
                . '<p>The present gross monthly salary of {{employee_name}} is <b>BDT {{salary}}</b> ({{salary_in_words}}).</p>'
                . '<p>This certificate is issued at the request of the employee for official purposes. The company bears no financial liability for any obligation entered into on the basis of this certificate.</p>'],

            'promotion' => ['Promotion', $to . $dear
                . '<p>We are pleased to inform you that, in recognition of your performance, dedication and contribution to {{company_name}}, the management has decided to promote you from <b>{{designation}}</b> to <b>{{new_designation}}</b> with effect from <b>{{effective_date}}</b>.</p>'
                . '<p>With this promotion, your gross monthly salary will be <b>BDT {{new_salary}}</b>. All other terms and conditions of your employment remain unchanged.</p>'
                . '<p>We are confident that you will take on your new responsibilities with the same commitment and continue to contribute to the growth of the company.</p>'
                . '<p>Congratulations, and best wishes for continued success.</p>' . $sincerely],

            'increment' => ['Salary Increment', $to . $dear
                . '<p>We are pleased to inform you that, on the basis of your performance review, the management has approved an increment of <b>BDT {{increment_amount}}</b> in your monthly salary with effect from <b>{{effective_date}}</b>.</p>'
                . '<p>Your revised gross monthly salary will be <b>BDT {{new_salary}}</b>. All other terms and conditions of your employment remain the same.</p>'
                . '<p>We appreciate your hard work and look forward to your continued contribution.</p>' . $sincerely],

            'transfer' => ['Transfer Order', $to . $dear
                . '<p>In the interest of the business, the management has decided to transfer you from {{branch_name}} to <b>{{transfer_branch}}</b> with effect from <b>{{effective_date}}</b>. You will continue as <b>{{designation}}</b> with your present salary and benefits.</p>'
                . '<p>Please hand over your current responsibilities, documents and company property to your department head before you leave, and report to the head of {{transfer_branch}} on the effective date.</p>'
                . '<p>We wish you success at your new place of work.</p>' . $sincerely],

            'department_change' => ['Change of Department', $to . $dear
                . '<p>This is to inform you that the management has decided to move you from the <b>{{department}}</b> department to the <b>{{new_department}}</b> department with effect from <b>{{effective_date}}</b>. Your designation, salary and other terms of employment remain unchanged.</p>'
                . '<p>Please complete the handover of your present duties and report to the head of the {{new_department}} department on the effective date.</p>'
                . '<p>We wish you success in your new role.</p>' . $sincerely],

            'designation_change' => ['Change of Designation', $to . $dear
                . '<p>This is to inform you that your designation has been changed from <b>{{designation}}</b> to <b>{{new_designation}}</b> with effect from <b>{{effective_date}}</b>. Your salary and other terms of employment remain unchanged unless informed otherwise in writing.</p>'
                . '<p>We trust you will carry out the responsibilities of your new designation with dedication.</p>' . $sincerely],

            'warning' => ['Letter of Warning', $to . $dear
                . '<p>It has been brought to the notice of the management that:</p>'
                . '<blockquote>{{reason}}</blockquote>'
                . '<p>This conduct is a breach of the rules and code of conduct of {{company_name}} and is not acceptable. You are hereby warned to refrain from such conduct in future.</p>'
                . '<p>Please note that any repetition will lead to strict disciplinary action, which may include termination of your employment, without further notice. A copy of this letter will be kept in your personal file.</p>' . $sincerely],

            'show_cause' => ['Show Cause Notice', $to . $dear
                . '<p>It has been reported to the management that:</p>'
                . '<blockquote>{{reason}}</blockquote>'
                . '<p>The above, if true, amounts to misconduct under the rules of the company and the Bangladesh Labour Act, 2006.</p>'
                . '<p>You are hereby directed to show cause in writing, on or before <b>{{reply_by}}</b>, why disciplinary action should not be taken against you. If no satisfactory reply is received by this date, the management will take such action as it deems fit without further reference to you.</p>' . $sincerely],

            'termination' => ['Termination of Employment', $to . $dear
                . '<p>This is to inform you that the management has decided to terminate your employment with {{company_name}} with effect from <b>{{effective_date}}</b>, for the following reason:</p>'
                . '<blockquote>{{reason}}</blockquote>'
                . '<p>Your last working day will be <b>{{last_working_day}}</b>. Your dues, if any, will be paid as per company policy and applicable law after you complete the clearance process.</p>'
                . '<p>Please return all company property, documents and your identity card to the HR department on or before your last working day.</p>' . $sincerely],

            'resignation_acceptance' => ['Acceptance of Resignation', $to . $dear
                . '<p>We acknowledge receipt of your resignation from the position of <b>{{designation}}</b> in the <b>{{department}}</b> department. The management has accepted your resignation, and your last working day with {{company_name}} will be <b>{{last_working_day}}</b>.</p>'
                . '<p>Please complete the handover of your duties and the clearance process before your last working day. Your final settlement will be made as per company policy after clearance.</p>'
                . '<p>We thank you for your contribution and wish you success in the future.</p>' . $sincerely],

            'relieving' => ['Relieving Letter', $to . $dear
                . '<p>With reference to your resignation, you are hereby relieved of your duties as <b>{{designation}}</b> in the <b>{{department}}</b> department of {{company_name}} at the close of business on <b>{{last_working_day}}</b>.</p>'
                . '<p>We confirm that you have handed over your responsibilities and the company property in your charge and have completed the clearance process. Your service with us was from {{joining_date}} to {{last_working_day}}.</p>'
                . '<p>We wish you all the best in your future career.</p>' . $sincerely],

            'noc' => ['No Objection Certificate', $concern
                . '<p>This is to certify that <b>{{employee_name}}</b>, Employee ID {{employee_id}}, has been working with {{company_name}} as <b>{{designation}}</b> in the <b>{{department}}</b> department since {{joining_date}}.</p>'
                . '<p>The company has no objection to the following:</p>'
                . '<blockquote>{{reason}}</blockquote>'
                . '<p>This certificate is issued at the request of the employee. The company accepts no financial or legal obligation arising from it.</p>'],

            'leave_approval' => ['Approval of Leave', $to . $dear
                . '<p>With reference to your leave application, we are pleased to inform you that your leave from <b>{{from_date}}</b> to <b>{{to_date}}</b> has been approved.</p>'
                . '<p><b>Type / reason of leave:</b> {{reason}}</p>'
                . '<p>Please hand over any urgent work to a colleague before you leave, and report back to duty on the next working day after your leave. Any extension must be approved in advance.</p>' . $sincerely],

            'confirmation' => ['Confirmation of Employment', $to . $dear
                . '<p>We are pleased to inform you that, on successful completion of your probation period, your employment as <b>{{designation}}</b> in the <b>{{department}}</b> department is confirmed with effect from <b>{{effective_date}}</b>.</p>'
                . '<p>All other terms and conditions of your appointment letter remain unchanged. You are now entitled to the benefits available to confirmed employees as per company policy.</p>'
                . '<p>We appreciate your performance and look forward to a long association.</p>' . $sincerely],

            'contract_renewal' => ['Renewal of Employment Contract', $to . $dear
                . '<p>We are pleased to inform you that your employment contract as <b>{{designation}}</b> has been renewed for a further period from <b>{{effective_date}}</b> to <b>{{contract_end}}</b>.</p>'
                . '<p>During the renewed term, your gross monthly salary will be <b>BDT {{new_salary}}</b>. All other terms and conditions of your earlier contract remain unchanged.</p>'
                . '<p>Please sign and return a copy of this letter as your acceptance of the renewal.</p>' . $sincerely],

            'suspension' => ['Suspension from Duty', $to . $dear
                . '<p>Pending an inquiry into the following allegation:</p>'
                . '<blockquote>{{reason}}</blockquote>'
                . '<p>you are hereby suspended from duty from <b>{{from_date}}</b> to <b>{{to_date}}</b>. During the suspension you will receive subsistence allowance as per company policy and the Bangladesh Labour Act, 2006.</p>'
                . '<p>During this period you must not enter the office or contact clients without written permission, and you must remain available for the inquiry when called.</p>' . $sincerely],

            'clearance' => ['Clearance Certificate', $concern
                . '<p>This is to certify that <b>{{employee_name}}</b>, Employee ID {{employee_id}}, former <b>{{designation}}</b> of the <b>{{department}}</b> department, has completed the clearance process with {{company_name}}.</p>'
                . '<p>All company property and documents have been returned and all dues settled. Nothing is outstanding against the employee as of the last working day, {{last_working_day}}.</p>'
                . '<p>This certificate is issued on request for official purposes.</p>'],

            'custom' => ['', $to . $dear . '<p>Write the letter here.</p>' . $sincerely],
        ];
    }
}
