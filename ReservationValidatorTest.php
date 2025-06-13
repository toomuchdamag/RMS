<?php
use PHPUnit\Framework\TestCase;

class ReservationValidatorTest extends TestCase {
    // Customer Name Validation Tests
    public function testValidateCustomerName_NullValue() {
        $this->assertFalse(ReservationValidator::validateCustomerName(null));
    }

    public function testValidateCustomerName_MinBoundary() {
        $this->assertTrue(ReservationValidator::validateCustomerName("A"));
    }

    public function testValidateCustomerName_InvalidChars() {
        $this->assertFalse(ReservationValidator::validateCustomerName("John123"));
    }

    public function testValidateCustomerName_NormalCase() {
        $this->assertTrue(ReservationValidator::validateCustomerName("John Smith"));
    }

    public function testValidateCustomerName_MaxBoundary() {
        $longName = str_repeat("a", 255);
        $this->assertTrue(ReservationValidator::validateCustomerName($longName));
    }

    public function testValidateCustomerName_MaxPlusOne() {
        $longName = str_repeat("a", 256);
        $this->assertFalse(ReservationValidator::validateCustomerName($longName));
    }

    public function testValidateCustomerName_MaxMinusOne() {
        $longName = str_repeat("a", 254);
        $this->assertTrue(ReservationValidator::validateCustomerName($longName));
    }

    public function testValidateCustomerName_ExtremeMax() {
        $longName = str_repeat("a", 1000);
        $this->assertFalse(ReservationValidator::validateCustomerName($longName));
    }

    public function testValidateCustomerName_MidLength() {
        $this->assertTrue(ReservationValidator::validateCustomerName("John Doe"));
    }

    public function testValidateCustomerName_SpecialChars() {
        $this->assertTrue(ReservationValidator::validateCustomerName("O'Connor"));
    }

    // Reservation Date Validation Tests
    public function testValidateReservationDate_NullValue() {
        $this->assertTrue(ReservationValidator::validateReservationDate(null));
    }

    public function testValidateReservationDate_Yesterday() {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $this->assertFalse(ReservationValidator::validateReservationDate($yesterday));
    }

    public function testValidateReservationDate_Today() {
        $today = date('Y-m-d');
        $this->assertTrue(ReservationValidator::validateReservationDate($today));
    }

    public function testValidateReservationDate_Tomorrow() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $this->assertTrue(ReservationValidator::validateReservationDate($tomorrow));
    }

    public function testValidateReservationDate_LeapDay() {
        $this->assertTrue(ReservationValidator::validateReservationDate("2024-02-29"));
    }
}
?>