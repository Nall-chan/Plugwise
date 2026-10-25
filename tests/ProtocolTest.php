<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';
include_once __DIR__ . '/../libs/Plugwise.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests für Protokollcode (libs/Plugwise.php) und den Receive-Filter der Device-Instanz.
 */
class ProtocolTest extends TestCase
{
    private const NodeMAC = '000D6F0000000001';

    /**
     * Der Splitter sendet die MAC hex-kodiert (bin2hex) an die Devices.
     * Der Receive-Filter der Device-Instanz muss genau diese Form treffen (P1-1).
     */
    public function testDeviceFilterMatchesSplitterFrame(): void
    {
        IPS\Kernel::reset();
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');
        $id = IPS_CreateInstance('{5FD73328-68F3-4047-B678-E385C2E31962}');
        IPS_SetProperty($id, 'NodeMAC', ' ' . strtolower(self::NodeMAC) . ' ');
        IPS_ApplyChanges($id);
        $Filter = IPS\InstanceManager::getInstanceInterface($id)->GetReceiveDataFilter();

        $Frame = new Plugwise\Frame(Plugwise\Command::AckMsgResponse, self::NodeMAC, '00');
        $this->assertSame(1, preg_match('/' . $Filter . '/', $Frame->ToJSONStringForDevices()), $Filter);

        $Other = new Plugwise\Frame(Plugwise\Command::AckMsgResponse, '000D6F0000000002', '00');
        $this->assertSame(0, preg_match('/' . $Filter . '/', $Other->ToJSONStringForDevices()));
    }

    public function testNodeTypeFromHex(): void
    {
        $this->assertSame(Plugwise\NodeTyp::CirclePlus, Plugwise\NodeTyp::FromHex('01'));
        $this->assertSame(Plugwise\NodeTyp::Circle, Plugwise\NodeTyp::FromHex('02'));
        $this->assertSame(Plugwise\NodeTyp::Scan, Plugwise\NodeTyp::FromHex('06'));
        $this->assertSame(Plugwise\NodeTyp::Stealth, Plugwise\NodeTyp::FromHex('09'));
        // Unbekannte Typen dürfen nicht zu einem Fehler führen.
        $this->assertSame(Plugwise\NodeTyp::Unknown, Plugwise\NodeTyp::FromHex('07'));
        $this->assertSame(Plugwise\NodeTyp::Unknown, Plugwise\NodeTyp::FromHex('FF'));
        $this->assertSame(Plugwise\NodeTyp::Unknown, Plugwise\NodeTyp::FromHex(''));
    }

    public function testFrequencyCode(): void
    {
        $this->assertSame(50.0, Plugwise\SwitchState::ToHertz(133));
        $this->assertSame(60.0, Plugwise\SwitchState::ToHertz(197));
        $this->assertNull(Plugwise\SwitchState::ToHertz(1));
    }

    public function testHex2TimestampInvalidInput(): void
    {
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('FFFFFFFF'));
    }

    public function testHex2TimestampRoundtrip(): void
    {
        // 2026-10-25 08:17 UTC
        $Timestamp = gmmktime(8, 17, 0, 10, 25, 2026);
        $Hex = substr(Plugwise\Frame::Timestamp2Hex($Timestamp), 0, 8);
        $this->assertSame($Timestamp, Plugwise\Frame::Hex2Timestamp($Hex));
    }

    public function testAckOutOfRangeContainsMac(): void
    {
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame('0000005D00E1000D6F00000000015E64');
        $this->assertSame(Plugwise\Command::AckMsgResponse, $Frame->Command);
        $this->assertSame(Plugwise\AckMsg::OUTOFRANGE, substr($Frame->Data, 0, 4));
    }
    public function testSenseConversion(): void
    {
        // Referenz: T = 175.72 * raw / 65536 - 46.85, H = 125 * raw / 65536 - 6
        $this->assertEqualsWithDelta(175.72 * 20000 / 65536 - 46.85, Plugwise\Sense::Temperature(20000), 0.0001);
        $this->assertEqualsWithDelta(125 * 30000 / 65536 - 6, Plugwise\Sense::Humidity(30000), 0.0001);
        // Die bisherige Formel des Moduls ergibt dieselben Werte.
        $this->assertEqualsWithDelta((20000 - 17473) / 372.90, Plugwise\Sense::Temperature(20000), 0.01);
        $this->assertEqualsWithDelta((30000 - 3145) / 524.30, Plugwise\Sense::Humidity(30000), 0.01);
        // 65535 = kein Wert
        $this->assertNull(Plugwise\Sense::Temperature(65535));
        $this->assertNull(Plugwise\Sense::Humidity(65535));
    }

    public function testPulsesConversion(): void
    {
        // 468.9385193 Pulse pro kW und Sekunde.
        $this->assertEqualsWithDelta(1000.0, Plugwise\Frame::pulsesToWatt(468.9385193), 0.001);
        $this->assertEqualsWithDelta(1.0, Plugwise\Frame::pulsesToKwh(468.9385193), 0.001);
        // Ohne Kalibrierung (gainA = 1, gainB = 0, Offsets 0) bleibt der Wert pro Sekunde erhalten.
        $this->assertEqualsWithDelta(100.0, Plugwise\Frame::pulsesCorrection('0320', 8, 0.0, 0.0, 1.0, 0.0), 0.0001);
        $this->assertSame(0.0, Plugwise\Frame::pulsesCorrection('0000', 1, 0.1, 0.1, 1.0, 1.0));
    }

    public function testHexToIntClampsOn32Bit(): void
    {
        $Max32 = 2147483647;
        $this->assertSame(1309164219, Plugwise\Frame::HexToInt(dechex(1309164219), $Max32));
        // Auf 32-Bit-Systemen liefert hexdec() ab 0x80000000 einen float; das Ergebnis wird begrenzt statt zu überlaufen.
        $this->assertSame($Max32, Plugwise\Frame::HexToInt('FFFFFFFF', $Max32));
        $this->assertSame(0, Plugwise\Frame::HexToInt('00000000', $Max32));
    }

    public function testValidMAC(): void
    {
        $this->assertTrue(Plugwise\Frame::IsValidMAC('000D6F0000000001'));
        $this->assertFalse(Plugwise\Frame::IsValidMAC('000d6f0000000001'));
        $this->assertFalse(Plugwise\Frame::IsValidMAC('000D6F000000000'));
        $this->assertFalse(Plugwise\Frame::IsValidMAC('000D6F000000000G'));
        $this->assertFalse(Plugwise\Frame::IsValidMAC('FFFFFFFF'));
    }

    public function testDateTimeData2Timestamp(): void
    {
        // Sekunde, Minute, Stunde, Wochentag, Tag, Monat, Jahr (Dezimalziffern); 2026-10-25 08:17:05 UTC
        $this->assertSame(gmmktime(8, 17, 5, 10, 25, 2026), Plugwise\Frame::DateTimeData2Timestamp('05170806251026'));
        $this->assertNull(Plugwise\Frame::DateTimeData2Timestamp('0517080625102'));
        $this->assertNull(Plugwise\Frame::DateTimeData2Timestamp('0517AB06251026'));
    }

    public function testWeekdayIsZeroBased(): void
    {
        // Montag = 0 (datetime.weekday()), Sonntag = 6. 2026-10-26 ist ein Montag, 2026-10-25 ein Sonntag.
        $this->assertSame('00', substr(Plugwise\Frame::Timestamp2Hex(gmmktime(8, 0, 0, 10, 26, 2026)), -2));
        $this->assertSame('06', substr(Plugwise\Frame::Timestamp2Hex(gmmktime(8, 0, 0, 10, 25, 2026)), -2));
    }
    public function testSwitchGroupData(): void
    {
        // Gruppe (1 Byte) und Zustand (1 Byte), Zustand ungleich 0 = ein (Scan: Bewegung).
        $this->assertSame(['Group' => 1, 'State' => true], Plugwise\SwitchState::FromGroupData('0101'));
        $this->assertSame(['Group' => 2, 'State' => false], Plugwise\SwitchState::FromGroupData('0200'));
        $this->assertSame(['Group' => 1, 'State' => true], Plugwise\SwitchState::FromGroupData('01FF'));
        $this->assertNull(Plugwise\SwitchState::FromGroupData('01'));
        $this->assertNull(Plugwise\SwitchState::FromGroupData('ZZ01'));
    }

    public function testKeyPressFrameIsDecodedWithGroupData(): void
    {
        // Event 0056, Frame-ID FFFF, Node-MAC, Gruppe 01, Zustand 01 (Checksumme wird hier nicht geprüft).
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame('0056FFFF000D6F00000000010101ABCD');
        $this->assertSame(Plugwise\Command::NodeSwitchGroupResponse, $Frame->Command);
        $this->assertSame('000D6F0000000001', $Frame->NodeMAC);
        $this->assertSame('0101', $Frame->Data);
    }

    public function testNodeAwakeText(): void
    {
        $this->assertSame('Button pressed', Plugwise\NodeAwake::ToString(Plugwise\NodeAwake::Button));
        $this->assertSame('Report state', Plugwise\NodeAwake::ToString(3));
        $this->assertSame('9', Plugwise\NodeAwake::ToString(9));
    }
    public function testHexToSigned(): void
    {
        // 16 Bit (pulse_1s, pulse_8s)
        $this->assertSame(3, Plugwise\Frame::HexToSigned('0003'));
        $this->assertSame(32767, Plugwise\Frame::HexToSigned('7FFF'));
        $this->assertSame(-32768, Plugwise\Frame::HexToSigned('8000'));
        $this->assertSame(-1, Plugwise\Frame::HexToSigned('FFFF'));
        // 32 Bit (Zähler), Werte aus echten Antworten: 00000003 und FFFFFFFE (= -2)
        $this->assertSame(3, Plugwise\Frame::HexToSigned('00000003'));
        $this->assertSame(-2, Plugwise\Frame::HexToSigned('FFFFFFFE'));
        $this->assertSame(188030, Plugwise\Frame::HexToSigned('0002DE7E'));
    }

    public function testNegativePulsesKeepSign(): void
    {
        // Speist ein Gerät zurück (z.B. Wechselrichter), sind die Pulse negativ: Betrag wie bei Verbrauch, Vorzeichen negativ.
        $this->assertEqualsWithDelta(-100.0, Plugwise\Frame::pulsesCorrection('FCE0', 8, 0.0, 0.0, 1.0, 0.0), 0.0001);
        $this->assertEqualsWithDelta(100.0, Plugwise\Frame::pulsesCorrection('0320', 8, 0.0, 0.0, 1.0, 0.0), 0.0001);
        // Ein kleiner negativer Zähler wird nicht zu einer riesigen Zahl (vorher: 4294967294 Pulse).
        $this->assertEqualsWithDelta(-2 / 3600, Plugwise\Frame::pulsesCorrection('FFFFFFFE', 3600, 0.0, 0.0, 1.0, 0.0), 0.000001);
        $this->assertLessThan(0.0, Plugwise\Frame::pulsesToKwh(Plugwise\Frame::pulsesCorrection('FFFF0000', 3600, 0.0, 0.0, 1.0, 0.0)));
        $this->assertEqualsWithDelta(-1000.0, Plugwise\Frame::pulsesToWatt(-468.9385193), 0.001);
    }
    public function testFeedInCounterOfInverter(): void
    {
        // Echte Antwort eines Circle hinter einem Wechselrichter: Zähler verbraucht 9, Zähler erzeugt FFFF5557 (= -43689 Pulse).
        $Produced = Plugwise\Frame::pulsesCorrection('FFFF5557', 3600, 0.0, 0.0, 1.0, 0.0);
        $FeedIn = -Plugwise\Frame::pulsesToKwh($Produced);
        // 43689 Pulse / (3600 * 468.9385193) = 0.0259 kWh, positiv dargestellt.
        $this->assertEqualsWithDelta(0.026, $FeedIn, 0.001);
        $this->assertGreaterThan(0.0, $FeedIn);
        // Pulse pro Sekunde und Leistung aus derselben Antwort: -153 Pulse in 8 s = ca. -40,8 W.
        $this->assertEqualsWithDelta(-40.8, Plugwise\Frame::pulsesToWatt(Plugwise\Frame::pulsesCorrection('FF67', 8, 0.0, 0.0, 1.0, 0.0)), 0.1);
    }
}
