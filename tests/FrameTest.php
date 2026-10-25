<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/autoload.php';
include_once __DIR__ . '/../libs/Plugwise.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests für Frames (Prüfsumme, Kodierung, Dekodierung) und den FormHelper.
 *
 * Die MAC-Adressen in den Tests sind erfunden. Die Frames ohne MAC sind unveränderte Mitschnitte (ACK des Sticks).
 */
class FrameTest extends TestCase
{
    private const FakeMAC = '000D6F0000000001';

    public function testReferenceImplementationCheckValue(): void
    {
        // Standard-Prüfwert für CRC-16/XMODEM.
        $this->assertSame(0x31C3, self::Crc16Xmodem('123456789'));
    }

    public static function captures(): array
    {
        return [
            'ACK Frame 93'  => ['0000005D00C1833D'],
            'ACK Frame 94'  => ['0000005E00C1296C'],
            'ACK Frame 669' => ['0000029D00C1E83D'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('captures')]
    public function testCapturedFrameChecksum(string $Capture): void
    {
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame($Capture);
        $this->assertTrue($Frame->Checksum);
        $this->assertSame(Plugwise\Command::AckMsgResponse, $Frame->Command);
        $this->assertSame(Plugwise\AckMsg::ACK, $Frame->Data);
    }

    public function testCapturedFrameWithWrongChecksum(): void
    {
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame('0000005D00C1833E');
        $this->assertFalse($Frame->Checksum);
    }

    public function testFrameIDs(): void
    {
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame('0000029D00C1E83D');
        $this->assertSame(0x029D, $Frame->FrameID);
    }

    public function testEncodedFrameMatchesReference(): void
    {
        // Anfrage an einen Node: Befehl + MAC + Daten, Prüfsumme über alles, abgeschlossen mit CRLF.
        $Frame = new Plugwise\Frame(Plugwise\Command::SwitchRequest, self::FakeMAC, Plugwise\SwitchState::On);
        $Payload = Plugwise\Command::SwitchRequest . self::FakeMAC . Plugwise\SwitchState::On;
        $this->assertSame(chr(5) . chr(5) . chr(3) . chr(3) . self::BuildFrame($Payload) . "\r\n", $Frame->EncodeFrame());
    }

    public function testEncodedFrameWithoutData(): void
    {
        $Frame = new Plugwise\Frame(Plugwise\Command::InfoRequest, self::FakeMAC);
        $Payload = Plugwise\Command::InfoRequest . self::FakeMAC;
        $this->assertSame(chr(5) . chr(5) . chr(3) . chr(3) . self::BuildFrame($Payload) . "\r\n", $Frame->EncodeFrame());
    }

    public function testDecodeAckWithMAC(): void
    {
        // ACK mit MAC: Antwort eines Nodes (z.B. OUT OF RANGE, wenn der Node nicht antwortet).
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame(self::BuildFrame('0000005D' . Plugwise\AckMsg::OUTOFRANGE . self::FakeMAC));
        $this->assertTrue($Frame->Checksum);
        $this->assertSame(Plugwise\Command::AckMsgResponse, $Frame->Command);
        $this->assertSame(Plugwise\AckMsg::OUTOFRANGE . self::FakeMAC, $Frame->Data);
    }

    public function testDecodeSwitchGroupEvent(): void
    {
        // Event NodeSwitchGroupResponse: Frame-ID FFFF, MAC, Gruppe 01, Zustand 01.
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame(self::BuildFrame('0056FFFF' . self::FakeMAC . '0101'));
        $this->assertTrue($Frame->Checksum);
        $this->assertSame(Plugwise\Command::NodeSwitchGroupResponse, $Frame->Command);
        $this->assertSame(self::FakeMAC, $Frame->NodeMAC);
        $this->assertSame(['Group' => 1, 'State' => true], Plugwise\SwitchState::FromGroupData($Frame->Data));
    }

    public function testDecodeNodeAwakeEvent(): void
    {
        $Frame = new Plugwise\Frame();
        $Frame->DecodeFrame(self::BuildFrame('004FFFFE' . self::FakeMAC . '05'));
        $this->assertTrue($Frame->Checksum);
        $this->assertSame(Plugwise\Command::NodeAwakeResponse, $Frame->Command);
        $this->assertSame('05', $Frame->Data);
        $this->assertSame(Plugwise\NodeAwake::Button, Plugwise\Frame::HexToInt($Frame->Data));
    }

    public function testCommandNames(): void
    {
        $this->assertSame('StickStatusResponse', Plugwise\Command::ToString(Plugwise\Command::StickStatusResponse));
        $this->assertSame('GetConnectedCirclePlus', Plugwise\Command::ToString(Plugwise\Command::GetConnectedCirclePlus));
        $this->assertSame('NodeAwakeResponse', Plugwise\Command::ToString(Plugwise\Command::NodeAwakeResponse));
        $this->assertSame('NodeSwitchGroupResponse', Plugwise\Command::ToString(Plugwise\Command::NodeSwitchGroupResponse));
        $this->assertSame('SensInfoResponse', Plugwise\Command::ToString(Plugwise\Command::SensInfoResponse));
    }

    public function testFormHelperFindsElementsByName(): void
    {
        $Helper = new class() {
            use Plugwise\FormHelper {
                SetFormProperty as public;
            }
        };
        $Form = [
            'elements' => [
                ['type' => 'RowLayout', 'items' => [['name' => 'Interval', 'type' => 'NumberSpinner']]],
                ['type' => 'ExpansionPanel', 'name' => 'Sens', 'items' => [['name' => 'showTemperature']]],
            ],
            'actions' => [
                ['type' => 'PopupButton', 'name' => 'RemoveNodePopup', 'popup' => ['items' => [['type' => 'List', 'name' => 'Nodes']]]],
            ],
        ];
        $this->assertTrue($Helper->SetFormProperty($Form, 'Sens', 'visible', false));
        $this->assertFalse($Form['elements'][1]['visible']);
        $this->assertTrue($Helper->SetFormProperty($Form, 'Nodes', 'values', [['NodeMAC' => self::FakeMAC]]));
        $this->assertSame([['NodeMAC' => self::FakeMAC]], $Form['actions'][0]['popup']['items'][0]['values']);
        $this->assertTrue($Helper->SetFormProperty($Form, 'Interval', 'enabled', false));
        $this->assertFalse($Form['elements'][0]['items'][0]['enabled']);
        $this->assertFalse($Helper->SetFormProperty($Form, 'Unknown', 'visible', false));
        $this->assertArrayNotHasKey('visible', $Form['elements'][0]);
    }
    public function testTimestampEncodingAtBeginningOfMonth(): void
    {
        // Ein Forenbeitrag meldete, dass die Uhr eines Nodes am 3. Dezember 2021 (18:44) auf den 01.01.2022 01:01 sprang.
        // Die Minuten seit Monatsanfang (4004 = 0xFA4) wurden nur dreistellig gesendet und verschoben das Telegramm.
        $Timestamp = gmmktime(18, 44, 0, 12, 3, 2021);
        $this->assertSame('150C0FA4' . 'FFFFFFFF' . '122C0004', Plugwise\Frame::Timestamp2Hex($Timestamp));
        // Erste Minute eines Monats.
        $this->assertSame('15010000' . 'FFFFFFFF' . '00000004', Plugwise\Frame::Timestamp2Hex(gmmktime(0, 0, 0, 1, 1, 2021) + 0));
    }

    public function testTimestampRoundtrip(): void
    {
        $Mismatches = [];
        for ($Timestamp = gmmktime(0, 0, 0, 1, 1, 2020); $Timestamp < gmmktime(0, 0, 0, 1, 1, 2032); $Timestamp += 3600 * 5 + 61) {
            $Encoded = Plugwise\Frame::Timestamp2Hex($Timestamp);
            $this->assertSame(24, strlen($Encoded), gmdate('c', $Timestamp));
            $Back = Plugwise\Frame::Hex2Timestamp(substr($Encoded, 0, 8));
            if ($Back !== $Timestamp - ($Timestamp % 60)) {
                $Mismatches[] = gmdate('c', $Timestamp);
            }
        }
        $this->assertSame([], array_slice($Mismatches, 0, 5));
    }

    public function testInvalidNodeTimeIsRejected(): void
    {
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('FFFFFFFF'));
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('15000000'));
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('150D0000'));
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('1501FFFF'));
        $this->assertSame(-1, Plugwise\Frame::Hex2Timestamp('xyz'));
    }

    public function testMissingCalibrationIsNotANumber(): void
    {
        // Fehlende Kalibrierwerte (FFFFFFFF) ergeben NAN; das Device darf sie nicht übernehmen.
        $this->assertTrue(is_nan(Plugwise\Frame::Hex2Float('FFFFFFFF')));
        $this->assertSame(0.0, Plugwise\Frame::Hex2Float('00000000'));
        $this->assertSame(1.0, Plugwise\Frame::Hex2Float('3F800000'));
    }
    public function testNodeTimeNeedsSync(): void
    {
        $Now = gmmktime(12, 14, 0, 10, 25, 2026);
        // Nach einem Stromausfall steht die Uhr des Nodes auf 01.01.2000 (so beobachtet an einem Circle+).
        $this->assertTrue(Plugwise\Frame::NodeTimeNeedsSync(Plugwise\Frame::Hex2Timestamp('14010000'), $Now));
        $this->assertTrue(Plugwise\Frame::NodeTimeNeedsSync(gmmktime(0, 0, 0, 1, 1, 2000), $Now));
        // Ungültige Zeit (FFFFFFFF ergibt -1) ebenfalls.
        $this->assertTrue(Plugwise\Frame::NodeTimeNeedsSync(Plugwise\Frame::Hex2Timestamp('FFFFFFFF'), $Now));
        // Kleine Abweichungen (Drift, Laufzeit) nicht.
        $this->assertFalse(Plugwise\Frame::NodeTimeNeedsSync($Now, $Now));
        $this->assertFalse(Plugwise\Frame::NodeTimeNeedsSync($Now - 120, $Now));
        $this->assertFalse(Plugwise\Frame::NodeTimeNeedsSync($Now + 300, $Now));
        $this->assertTrue(Plugwise\Frame::NodeTimeNeedsSync($Now - 301, $Now));
        $this->assertTrue(Plugwise\Frame::NodeTimeNeedsSync($Now - 3600, $Now));
    }

    /**
     * Unabhängige Umsetzung von CRC-16/XMODEM (Polynom 0x1021, Startwert 0), wie sie das Plugwise-Protokoll nutzt.
     */
    private static function Crc16Xmodem(string $Data): int
    {
        $Crc = 0x0000;
        for ($i = 0; $i < strlen($Data); $i++) {
            $Crc ^= ord($Data[$i]) << 8;
            for ($Bit = 0; $Bit < 8; $Bit++) {
                $Crc = ($Crc & 0x8000) ? ((($Crc << 1) ^ 0x1021) & 0xFFFF) : (($Crc << 1) & 0xFFFF);
            }
        }
        return $Crc;
    }

    private static function BuildFrame(string $Payload): string
    {
        return $Payload . sprintf('%04X', self::Crc16Xmodem($Payload));
    }
}
