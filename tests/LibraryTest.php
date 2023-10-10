<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateIO(): void
    {
        $this->validateModule(__DIR__ . '/../PlugwiseNetwork');
    }

    public function testValidateConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../PlugwiseConfigurator');
    }

    public function testValidateDevice(): void
    {
        $this->validateModule(__DIR__ . '/../PlugwiseDevice');
    }
}