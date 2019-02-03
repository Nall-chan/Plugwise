[![PHP-Modul](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Modul-Version](https://img.shields.io/badge/Modul%20Version-1.0-blue.svg)]()
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Symcon-Version](https://img.shields.io/badge/Symcon%20Version-4.3%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-4-3-%28Stable%29-Changelog)
[![StyleCI](https://styleci.io/repos/107307432/shield?style=flat)](https://styleci.io/repos/107307432)  

# IPSPlugwise

Implementierung von Plugwise in IP-Symcon.

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Vorbereitungen](#4-vorbereitungen)
5. [Einrichten der Instanzen in IPS](#5-einrichten-der--instanzen-in-ips)
6. [Anhang](#6-anhang)
    1. [GUID der Module](#1-guid-der-module)
    2. [Hinweise](#2-hinweise)
    3. [Changlog](#3-changlog)
    4. [Spenden](#4-spenden)
7. [Lizenz](#7-lizenz)

## 1. Funktionsumfang

### [Plugwise Konfigurator:](PlugwiseConfigurator/)  

 - Einrichten von Plugwise Geräten-Instanzen in IPS.  

### [Plugwise Network:](PlugwiseNetwork/)  

 - Kommunikation mit dem USB-Stick.  
 - An- und ablernen von Geräten über IPS.  

### [Plugwise Device:](PlugwiseDevice/)  

 - Darstellen der Messwerte in IPS.    
 - Darstellen und ansteuern des Schaltzustandes.  

## 2. Voraussetzungen

 - IPS ab Version 4.3
 - Plugwise USB-Stick

## 3. Installation

**IPS 4.3:**  
   Bei privater Nutzung: Über das Modul-Control folgende URL hinzufügen.  
   `git://github.com/Nall-chan/IPSPlugiwse.git`  

   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Vorbereitungen

 - Der USB-Stick und der Circle+ müssen gekoppelt sein.  
 - Die Plugwise Software darf nicht ausgeführt werden.

## 5. Einrichten der Instanzen in IPS

  Es wird empfohlen die Einrichtung mit dem Plugwise-Konfigurator durchzuführen.  
  
  - Auf der Willkommen-Seite von IPS dem Link 'Konfiguratoren verwalten' öffnen.  ![Konfiguratoren verwalten](Doku/Konfigurator0.png)  
  - Auf den Button 'Neu' klicken.  
  - Den Eintrag 'Plugwise Configurator' wählen und mit OK bestätigen.  
  - Die Instanz über einen weiten Klick auf OK erzeugen.  
  - Im folgenden Dialog des Konfigurators muss jetzt erst über einen Klick auf ein Zahnrad zum Splitter gewechselt werden.  
  - Im folgenden Dialog des Plugwise Network (der Splitter) muss jetzt erst über einen Klick auf ein Zahnrad zum IO gewechselt werden.  
  - Im Dialog des IO ist der COM-Port auszuwählen. Alle Dialoge sind nach dem Klick auf Übernehmen zu schließen.  
  - Die Instanz Plugwise Network sucht jetzt nach allen bekannten Geräten im Netz.
  - Das Ergebniss kann durch ein erneutes öffen des Konfigurators angezeigt werden.  
  - Dort kann über den Button 'Instant erzeugen' ein oben ausgewählten Gerät in IPS angelegt werden.  

## 6. Anhang

###  1. GUID der Module

| Modul                 | Typ          | Prefix   | GUID                                   |
| :-------------------: | :----------: | :------: | :------------------------------------: |
| Plugwise Device       | Device       | PLUGWISE | {5FD73328-68F3-4047-B678-E385C2E31962} |
| Plugwise Configurator | Configurator | PLUGWISE | {4C481455-ACE8-45FF-9C5E-02C8F70CEBC7} |
| Plugwise Network      | Splitter     | PLUGWISE | {7C20491F-F145-4F1C-A69C-AAE1F60F5BD5} |

### 2. Datenaustausch

| Parameter    | Typ     | Beschreibung                                              |
| :----------: | :-----: | :-------------------------------------------------------: |
| Command      | string  | Plugwise_Command 4 Byte                                   |
| NodeMAC      | string  | Quell/Ziel MAC des Node oder leer                         |
| Data         | string  | Payload  ohne NodeMac, Command und Checksumme             |

### 3. Changlog

Version 1.00:
 - Release

Version 0.98:  
 - Fixes für IPS 5.0  

Version 0.96:  
 - Erstes offizielles Release  

### 4. Spenden  
  
  Die Library ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
