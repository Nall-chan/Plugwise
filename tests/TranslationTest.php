<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';
include_once __DIR__ . '/../libs/Plugwise.php';

use PHPUnit\Framework\TestCase;

/**
 * Prüft, dass jede Beschriftung (Formular) und jeder per Translate() ausgegebene Text in der locale.json übersetzt ist.
 */
class TranslationTest extends TestCase
{
    public static function modules(): array
    {
        return [
            'Network'      => ['PlugwiseNetwork'],
            'Device'       => ['PlugwiseDevice'],
            'Configurator' => ['PlugwiseConfigurator'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('modules')]
    public function testFormCaptionsAreTranslated(string $Module): void
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/../' . $Module . '/form.json'), true);
        $Missing = array_values(array_diff(array_unique(self::FormTexts($Form)), self::Keys($Module)));
        $this->assertSame([], $Missing);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('modules')]
    public function testTranslateCallsAreTranslated(string $Module): void
    {
        $Code = file_get_contents(__DIR__ . '/../' . $Module . '/module.php');
        preg_match_all("/Translate\\('((?:[^'\\\\]|\\\\.)*)'\\)/", $Code, $Matches);
        $Texts = array_map(fn ($Text) => stripcslashes($Text), $Matches[1]);
        $Missing = array_values(array_diff(array_unique($Texts), self::Keys($Module)));
        $this->assertSame([], $Missing);
    }

    public function testNetworkStatesAreTranslated(): void
    {
        $Texts = [];
        foreach ((new ReflectionClass(Plugwise\NetworkState::class))->getConstants() as $State) {
            $Texts[] = Plugwise\NetworkState::ToString($State);
        }
        $this->assertSame([], array_values(array_diff($Texts, self::Keys('PlugwiseNetwork'))));
    }

    private static function Keys(string $Module): array
    {
        $Locale = json_decode(file_get_contents(__DIR__ . '/../' . $Module . '/locale.json'), true);
        return array_keys($Locale['translations']['de']);
    }

    private static function FormTexts(array $Form, array &$Texts = []): array
    {
        foreach ($Form as $Key => $Value) {
            if (is_array($Value)) {
                self::FormTexts($Value, $Texts);
            } elseif (in_array($Key, ['caption', 'label'], true) && is_string($Value) && ($Value !== '')) {
                $Texts[] = $Value;
            }
        }
        return $Texts;
    }
}
