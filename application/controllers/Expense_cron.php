<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expense_cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Expense_model');
        $this->load->model('Expense_occurrence_model');
    }

    public function generate_recurring_expenses()
    {
        if (!is_cli()) {
            echo "Access Denied: This method can only be executed via CLI.\n";
            return;
        }

        $expenses = $this->Expense_model->get_all_expenses();
        $generated_count = 0;
        $skipped_count = 0;

        foreach ($expenses as $expense) {
            // Under this architecture, last_payment_date will be NOT NULL. 
            // So we consistently generate from it, treating it dynamically as the baseline anchor.
            $base_date = $expense['last_payment_date'];
            
            // Calculate when the next occurrence should be
            $next_date = $this->_calculate_next_date($base_date, $expense['payment_type']);
            
            if ($this->Expense_occurrence_model->exists($expense['id'], $next_date)) {
                $skipped_count++;
                continue;
            }

            $data = [
                'expense_id'      => $expense['id'],
                'occurrence_date' => $next_date,
                'status'          => 'pending'
            ];

            if ($this->Expense_occurrence_model->insert_occurrence($data)) {
                $generated_count++;
                $this->db->where('id', $expense['id'])->update('expenses', ['last_payment_date' => $next_date]);
            }
        }
        
        echo "Cron complete. Generated: $generated_count. Skipped: $skipped_count\n";
    }

    private function _calculate_next_date($current_date_string, $payment_type)
    {
        $date = new DateTime($current_date_string);
        // We use the day of the last_payment_date implicitly to lock onto "their intended date schema"
        $original_day = (int) $date->format('d');

        if ($payment_type === 'daily') {
            $date->modify('+1 day');
            return $date->format('Y-m-d');
        }

        if ($payment_type === 'yearly') {
            $date->modify('+1 year');
            return $date->format('Y-m-d');
        }

        if ($payment_type === 'monthly') {
            $next_month = clone $date;
            $next_month->modify('first day of next month'); 
            $year = $next_month->format('Y');
            $month = $next_month->format('m');
            $days_in_next_month = (int) $next_month->format('t');
            $target_day = min($original_day, $days_in_next_month);
            $new_date = new DateTime(sprintf('%s-%s-%02d', $year, $month, $target_day));
            
            return $new_date->format('Y-m-d');
        }

        if ($payment_type === 'bi-monthly') {
            // Move to first day of month (+2 months from current anchor),
            // then clamp the original day-of-month within that month length.
            $next_month = clone $date;
            $next_month->modify('first day of next month');
            $next_month->modify('+1 month');

            $year = $next_month->format('Y');
            $month = $next_month->format('m');
            $days_in_next_month = (int) $next_month->format('t');
            $target_day = min($original_day, $days_in_next_month);
            $new_date = new DateTime(sprintf('%s-%s-%02d', $year, $month, $target_day));

            return $new_date->format('Y-m-d');
        }

        if ($payment_type === 'quarterly') {
            // Move to first day of month (+3 months from current anchor),
            // then clamp the original day-of-month within that month length.
            $next_month = clone $date;
            $next_month->modify('first day of next month');
            $next_month->modify('+2 months');

            $year = $next_month->format('Y');
            $month = $next_month->format('m');
            $days_in_next_month = (int) $next_month->format('t');
            $target_day = min($original_day, $days_in_next_month);
            $new_date = new DateTime(sprintf('%s-%s-%02d', $year, $month, $target_day));

            return $new_date->format('Y-m-d');
        }

        return $current_date_string; 
    }
}
