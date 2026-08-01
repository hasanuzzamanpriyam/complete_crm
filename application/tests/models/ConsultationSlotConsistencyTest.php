<?php
use PHPUnit\Framework\TestCase;

/**
 * Regression test for the slot timezone mismatch that made every offered
 * slot unbookable (HTTP 409) for consultants whose timezone differs from the
 * company timezone (Asia/Dhaka).
 *
 * get_available_slots() (the wizard display) and is_slot_available() (the
 * booking validator) must interpret slot times identically: in the
 * consultant's own timezone. If they disagree, the wizard shows slots that
 * can never be booked.
 *
 * Requires a booted CI instance (see run-model-tests.php).
 */
class ConsultationSlotConsistencyTest extends TestCase
{
    /** @var \Consultation_model|null */
    private static $model;

    public static function setUpBeforeClass(): void
    {
        $ci =& get_instance();
        if (!$ci) {
            throw new \RuntimeException('CodeIgniter is not booted; run via application/tests/models/run-model-tests.php');
        }
        $ci->load->model('consultation_model');
        self::$model = $ci->consultation_model;
    }

    private function consultantTz(): string
    {
        $consultant = self::$model->get_consultant(4);
        return !empty($consultant->timezone) ? $consultant->timezone : '';
    }

    private function offeredSlots(int $days = 21): array
    {
        $slots = array();
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime('+' . $i . ' days'));
            $offered = self::$model->get_available_slots(4, $date, 'Asia/Dhaka', 30);
            foreach ($offered as $slot) {
                $slots[] = $slot;
            }
        }
        return $slots;
    }

    public function testEveryOfferedSlotPassesAvailabilityValidation()
    {
        $consultant = self::$model->get_consultant(4);
        if (!$consultant || (int)$consultant->is_active !== 1) {
            $this->markTestSkipped('Active consultant 4 not found.');
        }

        $offered = $this->offeredSlots();
        foreach ($offered as $slot) {
            $ok = self::$model->is_slot_available(4, $slot['date'], $slot['time'], 'Asia/Dhaka', 30);
            $this->assertTrue(
                $ok,
                'Offered slot ' . $slot['date'] . ' ' . $slot['time'] . ' (Asia/Dhaka) was rejected by is_slot_available() (HTTP 409)'
            );
        }
    }

    public function testConsultantOffersAtLeastOneSlotInCustomerTimezone()
    {
        $consultant = self::$model->get_consultant(4);
        if (!$consultant || (int)$consultant->is_active !== 1) {
            $this->markTestSkipped('Active consultant 4 not found.');
        }

        $this->assertNotEmpty(
            $this->offeredSlots(),
            'Consultant ' . $consultant->name . ' (timezone ' . $this->consultantTz()
                . ') offers no bookable slots to Asia/Dhaka customers in the next 21 days.'
        );
    }
}
