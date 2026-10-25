[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FPlugwise%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-plugwise/142771)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FPlugwise%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#6-lizenz)
[![Check Style](https://github.com/Nall-chan/Plugwise/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Plugwise/actions) [![Run Tests](https://github.com/Nall-chan/Plugwise/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Plugwise/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#3-spenden)[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#3-spenden)

# Symcon-Modul: Plugwise <!-- omit in toc -->

Implementierung von Plugwise in IP-Symcon.

## Inhaltsverzeichnis  <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [Vorbemerkungen](#vorbemerkungen)
  - [Zur Library](#zur-library)
  - [Zur Integration von Geräten](#zur-integration-von-geräten)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Enthaltende Module](#4-enthaltende-module)
  - [Plugwise Konfigurator](#plugwise-konfigurator)
  - [Plugwise Network](#plugwise-network)
  - [Plugwise Device](#plugwise-device)
- [5. Anhang](#5-anhang)
  - [1. GUID der Module](#1-guid-der-module)
  - [2. Changelog](#2-changelog)
  - [3. Spenden](#3-spenden)
- [6. Lizenz](#6-lizenz)

## 1. Funktionsumfang

- Anbindung von Plugwise-Geräten (Circle+, Circle, Stealth, Scan, Sense und Switch) über den Plugwise USB-Stick.
- Darstellen der Messwerte (Leistung, Energie, Netzfrequenz, Temperatur, Luftfeuchtigkeit, Bewegung) in Variablen.
- Schalten von Circle, Circle+ und Stealth.
- An- und ab lernen von Geräten im Plugwise-Netzwerk.
- Einfaches Anlegen der Geräte-Instanzen in Symcon über einen Konfigurator.

## Vorbemerkungen

### Zur Library

> [!NOTE]
> Getestet wurde mit USB-Stick, Circle+ und Circle. Stealth, Scan, Sense und Switch werden nach dem Vorbild anderer Implementierungen unterstützt, wurden aber nicht mit echter Hardware getestet.

Ein Circle liefert Leistung und Energie, jedoch keine Spannung und keinen Strom.

### Zur Integration von Geräten

- Der USB-Stick und der Circle+ müssen gekoppelt sein
- Die Plugwise Software darf nicht gleichzeitig ausgeführt werden

## 2. Voraussetzungen

- Symcon ab Version 8.1
- Plugwise USB-Stick inklusive Circle+
- Weitere Plugwise-Geräte wie Circle, Stealth, Scan, Sense oder Switch

## 3. Software-Installation

Über den 'Module-Store' in Symcon das Modul 'Plugwise' hinzufügen.
**Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**
![Module-Store](imgs/install.png)

Nach der Installation über den 'Modul-Store' fragt Symcon ob die enthaltende [Konfiguratoren Instanz](PlugwiseConfigurator/) erstellt werden soll.
Das Anlegen der erforderlichen Instanzen wird in der Dokumentation des [Plugwise-Konfigurator](PlugwiseConfigurator/) beschrieben.

## 4. Enthaltende Module

### [Plugwise Konfigurator](PlugwiseConfigurator/)

Stellt alle in Symcon und im Plugwise-Netzwerk bekannten Geräte und Instanzen dar und legt Geräte-Instanzen in Symcon an.

### [Plugwise Network](PlugwiseNetwork/)

Kommuniziert mit dem USB-Stick, lernt Geräte im Plugwise-Netzwerk an und ab und verteilt die Nachrichten an die Geräte-Instanzen.

Datenaustausch zwischen Network und Gerät:

| Parameter |  Typ   |                 Beschreibung                  |
| :-------: | :----: | :-------------------------------------------: |
|  Command  | string |                Command 4 Byte                 |
|  NodeMAC  | string |       Quell/Ziel MAC des Node oder leer       |
|   Data    | string | Payload  ohne NodeMac, Command und Checksumme |

### [Plugwise Device](PlugwiseDevice/)

Stellt die Messwerte eines Gerätes in Variablen dar und schaltet das Gerät.

## 5. Anhang

### 1. GUID der Module

|         Modul         |     Typ      |  Prefix  |                  GUID                  |
| :-------------------: | :----------: | :------: | :------------------------------------: |
|    Plugwise Device    |    Device    | PLUGWISE | {5FD73328-68F3-4047-B678-E385C2E31962} |
| Plugwise Configurator | Configurator | PLUGWISE | {4C481455-ACE8-45FF-9C5E-02C8F70CEBC7} |
|   Plugwise Network    |   Splitter   | PLUGWISE | {7C20491F-F145-4F1C-A69C-AAE1F60F5BD5} |
### 2. Changelog

**Version 2.10:**

> [!IMPORTANT]
> Die Statusvariable `Firmware` der Instanz `Plugwise Network` war vom Typ String und ist jetzt vom Typ Integer. Beim Update wird die Variable neu angelegt und erhält eine neue Objekt-ID. Verknüpfungen, Archivierung und Skripte, die die alte ID verwenden, müssen angepasst werden.
> Fehler bei Funktionsaufrufen und Bedienaktionen werden als kurzer Text an den Aufrufer gemeldet (Frontend, Skript, Ereignis, Log).

**Neue Geräte:**

- Stealth wird erkannt und wie ein Circle behandelt

**Neue Statusvariablen:**

- Circle, Circle+ und Stealth: `Einspeisung Gesamt` (optional, standardmäßig aus), für Geräte die zurückspeisen, z.B. Wechselrichter
- Scan: `Bewegung`

**Geändertes Verhalten:**

- Antwortet ein Node nicht, wechselt seine Instanz in den Status `Node nicht erreichbar` und die Variable `Erreichbar` steht auf `Alarm`. Antwortet er wieder, geht beides zurück. Damit kann ein Watchdog oder die Ereignissteuerung reagieren
- Antwortet der Circle+ bei der Suche nach Nodes nicht, wird die Suche nach drei Versuchen mit dem Status `Circle+ offline` abgebrochen, statt sie endlos zu wiederholen
- Ungültige Node-MAC-Adressen werden bei den Funktionen zum Anlernen, Entfernen und Zurücksetzen abgelehnt
- Wird im Formular eine Anzeige-Option abgewählt, weist ein Hinweis darauf hin, dass die zugehörige Statusvariable gelöscht wird
- Die Uhr des Circle+ wird alle 10 Minuten geprüft und bei Abweichung (z.B. nach einem Stromausfall) neu gesetzt, `Circle+ Zeit okay` zeigt den aktuellen Zustand
- Hat ein Gerät seine Uhr verloren (z.B. nach einem Stromausfall), wird die Zeit sofort neu gesetzt und nicht erst zur nächsten vollen Stunde
- Eine Geräte-Instanz ohne MAC (z.B. per Skript angelegt) führt keine Abfragen aus

**Behobene falsche Werte in Statusvariablen:**

- `Erreichbar` zeigte nach einem Ausfall des Nodes weiter `OK` oder blieb dauerhaft auf `Alarm`
- Leistung und Verbrauch zeigten bei Geräten, die zurückspeisen (z.B. Wechselrichter), riesige Werte statt negativer Werte
- Fehlende Kalibrierdaten eines Gerätes führten zu absurden Werten bei Leistung und Verbrauch, sie werden jetzt erkannt und gemeldet
- Die Uhrzeit der Geräte wurde in den ersten drei Tagen eines Monats falsch gesetzt (z.B. auf den 01.01. 01:01 Uhr)
- Sense: Der Wert `65535` (kein Messwert) wird nicht mehr als Temperatur oder Luftfeuchte übernommen
- Scan und Switch: Die Meldungen wurden falsch ausgewertet und bei Sense, Scan und Switch kamen Ereignisse nicht an der Instanz an

**Fehlerbehebungen:**

- Der Konfigurator brach mit einem Fehler ab, wenn der Splitter keine Nodes lieferte
- Unbekannte Node-Typen und Messwerte führten zu Fehlern in der Instanz
- Fehler beim Verarbeiten eines Frames verwarfen die restlichen empfangenen Frames
- Die Darstellung der Variable `Erreichbar` und weiterer Variablen verwendete Parameter, die Symcon für diese Darstellung nicht kennt

**Version 2.00:**  

- Kommunikation mit dem Stick verbessert  
- Statusabfrage der Nodes erfolgt erst nachdem die Netzwerksuche abgeschlossen wurde  
- Debug Ausgaben angepasst, um bei Fehlern besser analysieren zu können  
- Berechnung von Energy Variablen angepasst  
- Konfigurationsformulare überarbeitet  
- Variablenhandling angepasst  
- Umstellung von Profile auf Darstellungen  
- Statusvariable für Erreichbar ergänzt  
- Übersetzungen ergänzt  
- Dokumentation überarbeitet  
  
**Version 1.10:**  

- Beta Version zur Vorbereitung des Refactoring  
- Diverse kleine Bugfixes  
  
**Version 1.00:**  

- Release

**Version 0.98:**  

- Fixes für Symcon 5.0  

**Version 0.96:**  

- Erstes offizielles Release  

### 3. Spenden  
  
Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

PayPal:  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)  

Wunschliste:  
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)  

## 6. Lizenz

IPS-Modul:  
[Custom NC-SA](LICENSE)  
