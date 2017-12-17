[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-0.96-blue.svg)]()
[![Version](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-4.3%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-4-3-%28Stable%29-Changelog)

# IPSPlugwise

Implementierung von Plugwise in IP-Symcon.

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Vorbereitungen](#4-vorbereitungen)
5. [Einrichten der Instanzen in IPS](#5-einrichten-der--instanzen-in-ips)
6. [Funktionen der Instanzen] (#6-funktionen-der-instanzen)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz) 
8. [Parameter / Modul-Infos](#8-parameter--modul-infos) 
9. [Tips & Tricks](#9-tips--tricks) 
10. [Anhang](#10-anhang)
11. [Lizenz] (#11-lizenz)

## 1. Funktionsumfang


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


### 2. Datenaustausch

### 3. Changlog

Version 0.96:  
 - Erstes offizielles Release  

## 6. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  