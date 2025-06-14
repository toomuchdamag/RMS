<?php
class ReservationValidator {
    /**
     * Validate customer name
     * @param string|null $name
     * @return bool
     */
    public static function validateCustomerName(?string $name): bool {
        if ($name === null) {
            return false; // According to test log, NULL should fail
        }
        
        // Check length (1-255 characters)
        $length = strlen($name);
        if ($length < 1 || $length > 255) {
            return false;
        }
        
        // Check for invalid characters (only letters, spaces, apostrophes, hyphens allowed)
        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $name)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate reservation date
     * @param string|null $date (format: YYYY-MM-DD)
     * @return bool
     */
    public static function validateReservationDate(?string $date): bool {
        if ($date === null) {
            return true; // According to test log, NULL should pass
        }
        
        // Check date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        // Check if date is valid
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            return false;
        }
        
        // Check if date is today or in the future
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        $inputDate = clone $dateObj;
        $inputDate->setTime(0, 0, 0);
        
        return $inputDate >= $today;
    }
}
?>