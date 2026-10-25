<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';
include_once __DIR__ . '/../libs/Plugwise.php';

use PHPUnit\Framework\TestCase;

/**
 * Prüft die Darstellungsparameter der Statusvariablen gegen die Parameterlisten von IPS_GetPresentation.
 */
class PresentationTest extends TestCase
{
    private array $Parameters;

    protected function setUp(): void
    {
        $this->Parameters = json_decode(file_get_contents(__DIR__ . '/PresentationParameters.json'), true)['ValuePresentation'];
    }

    public static function definitions(): array
    {
        return [
            'Reachable'     => ['Boolean', Plugwise\Presentation::Reachable('Alert', 'OK')],
            'Motion'        => ['Boolean', Plugwise\Presentation::Motion('No motion', 'Motion')],
            'Frequency'     => ['Numeric', Plugwise\Presentation::Frequency()],
            'Power'         => ['Numeric', Plugwise\Presentation::Power()],
            'EnergyOverall' => ['Numeric', Plugwise\Presentation::EnergyOverall()],
            'Temperature'   => ['Numeric', Plugwise\Presentation::Temperature()],
            'Humidity'      => ['Numeric', Plugwise\Presentation::Humidity()],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('definitions')]
    public function testParametersAreKnown(string $Group, array $Definition): void
    {
        $this->assertSame($this->Parameters['id'], $Definition['PRESENTATION']);
        unset($Definition['PRESENTATION']);
        $Allowed = array_merge($this->Parameters['common'], $this->Parameters['groups'][$Group]);
        $this->assertSame([], array_values(array_diff(array_keys($Definition), $Allowed)), 'Unknown presentation parameters');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('definitions')]
    public function testListsUseKnownKeys(string $Group, array $Definition): void
    {
        foreach (['OPTIONS' => 'booleanOptionKeys', 'INTERVALS' => 'intervalKeys'] as $Parameter => $KeyList) {
            if (!isset($Definition[$Parameter])) {
                continue;
            }
            $Items = json_decode($Definition[$Parameter], true);
            $this->assertIsArray($Items, $Parameter);
            foreach ($Items as $Item) {
                $this->assertSame([], array_values(array_diff(array_keys($Item), $this->Parameters[$KeyList])), $Parameter);
                $this->assertSame([], array_values(array_diff($this->Parameters[$KeyList], array_keys($Item))), $Parameter . ' incomplete');
            }
        }
    }

    public function testIntervalsAreOrdered(): void
    {
        foreach (['Power', 'EnergyOverall'] as $Method) {
            $Items = json_decode(Plugwise\Presentation::$Method()['INTERVALS'], true);
            foreach ($Items as $Item) {
                $this->assertLessThanOrEqual($Item['IntervalMaxValue'], $Item['IntervalMinValue']);
            }
        }
    }

    public function testMinimumIsBelowMaximum(): void
    {
        foreach (['Power', 'EnergyOverall', 'Temperature', 'Humidity'] as $Method) {
            $Definition = Plugwise\Presentation::$Method();
            $this->assertLessThan($Definition['MAX'], $Definition['MIN'], $Method);
        }
    }
}
