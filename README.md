# IPSKodi

Implementierung der Kodi JSON-RPC API in IP-Symcon.

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

 Ermöglicht das Steuern und das empfangen von Statusänderungen, von der Mediacenter-Software Kodi über das Netzwerk.
 Direkte Bedienung im WebFront möglich.
 Abbilden fast der gesamten Kodi-API in vollen Funktionsumfangs in PHP-Befehlen für eigene Scripte in IPS.
 Folgende Namespaces der API wurden aktuell nicht berücksichtigt, sollte hier Bedarf bestehen, so können Diese noch nachgepflegt werden:
- Profiles  
- Settings  
- Textures  
- XBMC  

## 2. Voraussetzungen

 - IPS ab Version 4.1
 - Installiertes System mit der Software Kodi 


## 3. Installation

**IPS 4.1:**  
   Bei privater Nutzung: Über das Modul-Control folgende URL hinzufügen.  
   `git://github.com/Nall-chan/IPSKodi.git`  

   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Vorbereitungen

 In den Kodi-Systemen folgende Einstellungen vornehmen:

 - In Settings/Services/Remote Control
    - Allow programs on other systems to control Kodi.
 - In Settings/Services/Webserver
    - Allow control of Kodi via HTTP
    - Username und Password nach eigenen Ermessen.


## 5. Einrichten der Instanzen in IPS

  Es wird empfohlen die Einrichtung mit dem Kodi-Konfigurator durchzuführen.  
  
  - Auf der Willkommen-Seite von IPS dem Link 'Konfiguratoren verwalten' öffnen.  ![Konfiguratoren verwalten](Doku/Konfigurator0.png)  
  - Auf den Button 'Neu' klicken.  
  - Den Eintrag 'Kodi Konfigurator' wählen und mit OK bestätigen.  ![Konfigurator wählen](Doku/Konfigurator1.png)  
  - Die Instanz über einen weiten Klick auf OK erzeugen.  
  - Im folgenden Dialog des Konfigurators muss jetzt erst über einen Klick auf ein Zahnrad zum Splitter gewechselt werden.  ![Zum Splitter wechseln](Doku/Konfigurator2.png)  
  - Im Dialog des Kodi-Splitter ist die Adresse des Zielsystems einzutragen. Handelt es sich um ein System welches nicht im Dauerbetrieb läuft, sollte der Haken bei 'Erweiterte Power-On Überwachung' gesetzt werden. Der Dialog kann nach dem Klick auf Übernehmen geschlossen werden.  ![Splitter konfigurieren](Doku/Splitter.png)  
  - Im Konfigurator durch einen Klick auf den Button 'Alle / Alle fehlenden' werden jetzt alle Instanzen für dieses Kodi-System erzeugt und im Root des logischen Baums angelegt.  ![Instanzen erzeugen](Doku/Konfigurator3.png)  

## 6. Funktionen der Instanzen

Jeder Typ von Instanz bildet einen bestimmen Funktionsbereich der Kodi-API ab.

 **Kodi Addons (KodiDeviceAddons):**  
 RPC-Namensraum : Addons
 
 Addons                 - de/aktivieren, lesen, visualisieren und ausführen.  
 
---

 **Kodi Anwendung (KodiDeviceApplication):**  
 RPC-Namensraum : Application
 
 Lautstärke             - Setzen, lesen und visualisieren.  
 Stummschaltung         - Setzen, lesen und visualisieren.  
 Software beenden       - Nur ausführen.  
 Namen der Software     - Lesen und visualisieren.  
 Version der Software   - Lesen und visualisieren.  

---

 **Kodi Audio Datenbank (KodiDeviceAudioLibrary):**
 RPC-Namensraum : AudioLibrary

 Künstler   - Lesen von Daten aus der Datenbank.  
 Alben      - Lesen von Daten aus der Datenbank.  
 Songs      - Lesen von Daten aus der Datenbank.  
 Datenbank  - Ausführen von Scan un Clean. Status visualisieren.

Das Setzen von Daten in der Datenbank ist nicht möglich!  

---  
**Kodi Favoriten (KodiDeviceFavourites):**  
 RPC-Namensraum : Favourites  

 Favoriten   - Lesen, visualisieren und ausführen.

---

 **Kodi Files (KodiDeviceFiles):**
 RPC-Namensraum : Files  

 Quellen       - Lesen aller bekannten Medienquellen.
 Verzeichnisse - Auslesen von Verzeichnissen.
 Dateien       - Auslesen von Eigenschaften einer Datei.  

---

 **Kodi GUI (KodiDeviceGUI):**  
 RPC-Namensraum : GUI  

 Aktuelles Fenster  - Lesen und visualisieren.  
 Aktuelle Steuerung - Lesen und visualisieren.  
 Aktueller Skin     - Lesen und visualisieren.  
 Vollbildmodus      - Setzen, lesen und visualisieren.  
 Bildschrimschoner  - Status visualisieren.  
 Benachrichtungen   - Senden.  

Hinweise zu den 'Window IDs' und 'Window Name' sind hier verfügbar:  
[Kodi Website - Window IDs] (http://kodi.wiki/view/Window_IDs)  

---

 **Kodi Input (KodiDeviceInput):**  
 RPC-Namensraum : Input  
  
 Tastendruck    - Senden  
 Text           - Senden  

---

 **Kodi Playerstatus (KodiDevicePlayer):**  
 PRC-Namensraum : Player  
 Hinweis: Jeder 'Player' (Audio,Video,Bilder) benötigt eine eigene Instanz.  
 Player              - Setzen, lesen und visualisieren des Status.  
 Aktuelle Wiedergabe - Setzen, lesen und visualisieren.  

---

 **Kodi Playlist (KodiDevicePlaylist):**  
 PRC-Namensraum : Playlist  
 Hinweis: Jeder 'Player' (Audio,Video,Bilder) hat immer eine eigene Playlist!  

 Playlist           - Beschreiben, lesen und visualisieren.  
 Player             - Direktes anspringen eines Eintrages.
 
---

 **Kodi PVR (KodiDevicePVR):**  
 RPC-Namensraum : PVR  

 Verfügbarkeit      - Zustand lesen und visualisieren.  
 Suchlauf           - Starten, Zustand lesen und visualisieren.  
 Aufnahme           - Steuern, Zustand lesen und visualisieren.  
 Kanäle & Gruppen   - Lesen und visualisieren (inkl. umschalten von Kanälen.)  
 Aufnahmen          - Lesen und visualisieren (inkl. starten der Wiedergabe.)  
 Timer              - Lesen  

 ---

 **Kodi System (KodiDeviceSystem):**  
 RPC-Namensraum : System  

 Systemzustand  - Starten, Beenden, Status visualisieren.  
 Optisches LW   - Auswerfen  

---

 **Kodi VideoLibrary (KodiDeviceVideoLibrary):**  
 RPC-Namensraum : VideoLibrary  

 Filme      - Lesen von Daten aus der Datenbank.  
 Serien     - Lesen von Daten aus der Datenbank.  
 Musikvideo - Lesen von Daten aus der Datenbank.  
 Datenbank  - Ausführen von Scan und Clean. Status visualisieren.

Das Setzen von Daten in der Datenbank ist nicht möglich!  

---

 **Kodi Splitter (KodiSplitter):**  
 RPC-Namensraum : JSONRPC  

## 7. PHP-Befehlsreferenz

 **Kodi Addons (KodiDeviceAddons):**  

```php
boolean KODIADDONS_ExecuteAddon(integer $InstanzeID, string $AddonId);
```
 Startet das in $AddonId übergebene Addon.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIADDONS_ExecuteAddonWait(integer $InstanzeID, string $AddonId);
```
 Startet das in $AddonId übergebene Addon und wartet auf die Ausführung.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIADDONS_ExecuteAddonEx(integer $InstanzeID, string $AddonId, string $Param);
```
 Startet das in $AddonId übergebene Addon mit Parametern. 
 Die zu übergebenden Parameter an das AddOn müssen als JSON-String in $Params übergeben werden.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIADDONS_ExecuteAddonExWait(integer $InstanzeID, string $AddonId, string $Param);
```
 Startet das in $AddonId übergebene Addon mit Parametern und wartet auf die Ausführung.  
 Die zu übergebenden Parameter an das AddOn müssen als JSON-String in $Params übergeben werden.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
array|boolean KODIADDONS_GetAddonDetails(integer $InstanzeID, string $AddonId);
```
 Liest die Eigenschaften eines Addons aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung               |
|:-------------------------:|:---------:|:--------------------------:|
| addonid                   | string    |                            |
| disclaimer                | string    |                            |
| fanart                    | string    | Pfad zum Fanart            |
| broken                    | mixed     |                            |
| author                    | string    |                            |
| enabled                   | boolean   |                            |
| extrainfo                 | array     |                            |
| thumbnail                 | string    | Pfad zum Cover             |
| path                      | string    |                            |
| dependencies              | array     |                            |
| type                      | array     |                            |
| description               | string    | Beschreibung               |
| name                      | string    |                            |
| version                   | string    |                            |
| summary                   | string    |                            |
| rating                    | integer   | Bewertung                  |

```php
array|boolean KODIADDONS_GetAddons(integer $InstanzeID);
```
 Liest die Eigenschaften aller Addons aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIADDONS_GetAddonDetails.  

```php
boolean KODIADDONS_SetAddonEnabled(integer $InstanzeID, boolean $Value);
```
 De-/Aktiviert ein Addon.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.


 **Kodi Anwendung (KodiDeviceApplication):**  
```php
boolean KODIAPP_SetMute(integer $InstanzeID, boolean $Value);
```
 De-/Aktiviert die Stummschaltung.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIAPP_SetVolume(integer $InstanzeID, integer $Value);
```
 Setzen der Lautstärke (0 - 100).  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIAPP_Quit(integer $InstanzeID);
```
 Beendet die Kodi-Anwendung.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIAPP_RequestState(integer $InstanzeID, string $Ident);
```
 Frage einen Wert ab.  
 Es ist der Ident der Statusvariable zu übergeben ("volume","muted","name","version")  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

---

 **Kodi Audio Datenbank (KodiDeviceAudioLibrary):**
```php
boolean KODIAUDIOLIB_Scan(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIAUDIOLIB_Clean(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIAUDIOLIB_Export(integer $InstanzeID, string $Path, boolean $Overwrite, boolean $includeImages);
```
 Exportiert die Audio Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
array|boolean KODIAUDIOLIB_GetAlbumDetails(integer $InstanzeID, integer $AlbumID);
```
 Liest die Eigenschaften eines Album aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung               |
|:-------------------------:|:---------:|:--------------------------:|
| theme                     | string[]  |                            |
| description               | string    | Beschreibung               |
| type                      | string    |                            |
| style                     | string[]  | Array der Stiele           |
| albumid                   | integer   |                            |
| playcount                 | integer   | Anzahl der Wiedergaben     |
| albumlabel                | string    |                            |
| mood                      | string[]  | Array der Stimmungen       |
| displayartist             | string    | Künstler                   |
| artist                    | string[]  | Array der Künstler         |
| genreid                   | integer[] | Array der Genre IDs        |
| musicbrainzalbumartistid  | string    | Music Brainz AlbumArtistID |
| year                      | integer   | Erscheinungsjahr           |
| rating                    | integer   | Bewertung                  |
| artistid                  | integer[] | Array der Künstler IDs     |
| title                     | string    | Titel des Album            |
| musicbrainzalbumid        | string    | Music Brainz AlbumID       |
| genre                     | string[]  | Array der Genres           |
| fanart                    | string    | Pfad zum Fanart            |
| thumbnail                 | string    | Pfad zum Cover             |

```php
array|boolean KODIAUDIOLIB_GetAlbums(integer $InstanzeID);
```
 Liest einen Teil der Eigenschaften aller Alben aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.  

```php
array|boolean KODIAUDIOLIB_GetRecentlyAddedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Alben aus, welche zuletzt zur Datenbank hinzugefügt wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.  

```php
array|boolean KODIAUDIOLIB_GetRecentlyPlayedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Alben aus, welche zuletzt zur wiedergegeben wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.  

```php
array|boolean KODIAUDIOLIB_GetArtistDetails(integer $InstanzeID, integer $ArtistID);
```
 Liest die Eigenschaften eines Künstlers aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                 | Typ       | Beschreibung      |
|:---------------------:|:---------:|:-----------------:|
| born                  | string    |                   |
| formed                | string    |                   |
| died                  | string    |                   |
| style                 | string[]  |                   |
| yearsactive           | string[]  |                   |
| mood                  | string[]  |                   |
| musicbrainzartistid   | string[]  |                   |
| disbanded             | string    |                   |
| description           | string    |                   |
| artist                | string    |                   |
| instrument            | string[]  |                   |
| artistid              | integer   |                   |
| genre                 | string[]  | Array der Genres  |
| fanart                | string    | Pfad zum Fanart   |
| thumbnail             | string    | Pfad zum Cover    |

```php
array|boolean KODIAUDIOLIB_GetArtists(integer $InstanzeID);
```
 Liest die Eigenschaften aller Künstler aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetArtistDetails.  

```php
array|boolean KODIAUDIOLIB_GetGenres(integer $InstanzeID);
```
 Liest die Eigenschaften aller bekannten Genres aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 
| Index     | Typ     | Beschreibung    |
|:---------:|:-------:|:---------------:|
| genreid   | integer | ID des Genres   |
| fanart    | string  | Pfad zum Fanart |
| thumbnail | string  | Pfad zum Cover  |

```php
array|boolean KODIAUDIOLIB_GetSongDetails(integer $InstanzeID, integer $SongID);
```
 Liest die Eigenschaften eines Songs aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| lyrics                    | string    |                               |
| songid                    | integer   |                               |
| albumartistid             | integer[] |                               |
| disc                      | integer   |                               |
| comment                   | string    |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| album                     | string    |                               |
| file                      | string    |                               |
| lastplayed                | string    |                               |
| albumid                   | integer   |                               |
| musicbrainzartistid       | string    |                               |
| albumartist               | string[]  |                               |
| duration                  | integer   |                               |
| musicbrainztrackid        | string    |                               |
| track                     | integer   |                               |
| displayartist             | string    | Künstler                      |
| artist                    | string[]  |                               |
| genreid                   | integer[] | Array der Genre IDs           |
| musicbrainzalbumartistid  | string    | Music Brainz AlbumArtistID    |
| year                      | integer   | Erscheinungsjahr              |
| rating                    | integer   | Bewertung                     |
| artistid                  | integer[] | Array der Künstler IDs        |
| title                     | string    | Titel des Album               |
| musicbrainzalbumid        | string    | Music Brainz AlbumID          |
| genre                     | string[]  | Array der Genres              |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIAUDIOLIB_GetSongs(integer $InstanzeID);
```
 Liest die Eigenschaften aller Songs aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.  

```php
array|boolean KODIAUDIOLIB_GetRecentlyAddedSongs(integer $InstanzeID);
```
 Liest die Eigenschaften der Songs aus, welche zuletzt zur Datenbank hinzugefügt wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.  

```php
array|boolean KODIAUDIOLIB_GetRecentlyPlayedSongs(integer $InstanzeID);
```
 Liest die Eigenschaften der Songs aus, welche zuletzt zur wiedergegeben wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.  

---

**Kodi Favoriten (KodiDeviceFavourites):**  

```php
array|boolean KODIFAV_GetFavourites(integer $InstanzeID, string $Type);
```
 Liest die Eigenschaften der Favoriten aus, welche dem im $Type übergeben Typen angehören.  
 $Type kann dabei "media", "window", "script" oder "unknown" annehmen.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 
| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|


---

 **Kodi Files (KodiDeviceFiles):**  

 ```php
array|boolean KODIFILES_GetSources(integer $InstanzeID, string $Media);
```
 Liefert alle bekannten Quellen nach Typ $Value.
 $Media: Der Typ der zu suchenden Quellen.  
    "video"=Video  
    "music"=Musik  
    "pictures"=Bilder  
    "files"=Dateien  
    "programs"=Programme  
 Rückgabewert ist ein Array mit den Quellen oder FALSE bei einem Fehler.  

| Index   | Typ     | Beschreibung                  |
|:-------:|:-------:|:-----------------------------:|
| file    | string  | Verzeichniss der Quelle       |
| label   | string  | Name der Quelle               |

 ```php
array|boolean KODIFILES_GetFileDetails(integer $InstanzeID, string $File, string $Media);
```
 Liefert alle Details einer Datei.  
 $File : Dateiname  
 $Media: Der Typ der zu suchenden Quellen.  
    "video"=Video  
    "music"=Musik  
    "pictures"=Bilder  
    "files"=Dateien  
    "programs"=Programme  
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| filetyp                   | string    |                               |
| size                      | integer   |                               |
| mimetype                  | integer   |                               |
| file                      | string    |                               |
| lastmodified              | string    |                               |
| sorttitle                 | string    |                               |
| productioncode            | string    |                               |
| cast                      | array     |                               |
| votes                     | string    |                               |
| duration                  | integer   |                               |
| trailer                   | string    |                               |
| albumid                   | integer   |                               |
| musicbrainzartistid       | string    |                               |
| mpaa                      | string    |                               |
| albumlabel                | string    |                               |
| originaltitle             | string    |                               |
| writer                    | string[]  |                               |
| albumartistid             | integer[] |                               |
| type                      | string    |                               |
| episode                   | integer   |                               |
| firstaired                | string    |                               |
| showtitle                 | string    |                               |
| country                   | string[]  ]                               |
| mood                      | string[]  |                               |
| set                       | string    |                               |
| musicbrainztrackid        | string    |                               |
| tag                       | string[]  |                               |
| lyrics                    | string    |                               |
| top250                    | integer   |                               |
| comment                   | string    |                               |
| premiered                 | string    |                               |
| showlink                  | string[]  |                               |
| style                     | string[]  |                               |
| album                     | string    |                               |
| tvshowid                  | integer   |                               |
| season                    | integer   |                               |
| theme                     | string[]  |                               |
| description               | string    |                               |
| setid                     | integer   |                               |
| track                     | integer   |                               |
| tagline                   | string    |                               |
| plotoutline               | string    |                               |
| watchedepisodes           | integer   |                               |
| id                        | integer   |                               |
| disc                      | integer   |                               |
| albumartist               | string[]  |                               |
| studio                    | string[]  |                               |
| uniqueid                  | array     |                               |
| episodeguide              | string    |                               |
| imdbnumber                | string    |                               |
| dateadded                 | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| streamdetails             | array     |                               |
| director                  | string[]  |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| displayartist             | string    | Künstler                      |
| artist                    | string[]  | Array der Künstler            |
| genreid                   | integer[] | Array der Genre IDs           |
| musicbrainzalbumartistid  | string    | Music Brainz AlbumArtistID    |
| year                      | integer   | Erscheinungsjahr              |
| rating                    | integer   | Bewertung                     |
| artistid                  | integer[] | Array der Künstler IDs        |
| title                     | string    | Titel der Datei               |
| musicbrainzalbumid        | string    | Music Brainz AlbumID          |
| genre                     | string[]  | Array der Genres              |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

 ```php
array|boolean KODIFILES_GetDirectory(integer $InstanzeID, string $Directory);
```
 Liefert Informationen zu einem Verzeichnis.  
 $Directory : Verzeichnis  
 Rückgabewert ist ein Array mit den Eigenschaften des Verzeichnises FALSE bei einem Fehler.  
 Es gilt die Tabelle von KODIFILES_GetFileDetails.  

 ```php
array|boolean KODIFILES_GetDirectoryDetails(integer $InstanzeID, string $Directory, string $Media);
```
 Liefert alle Details eines Verzeichnisses.  
 $Directory : Verzeichnis  
 $Media: Der Typ der zu suchenden Quellen.  
    "video"=Video  
    "music"=Musik  
    "pictures"=Bilder  
    "files"=Dateien  
    "programs"=Programme  
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.  
 Es gilt die Tabelle von KODIFILES_GetFileDetails.  
---
 
 **Kodi GUI (KodiDeviceGUI):**  

```php
boolean KODIGUI_SetFullscreen(integer $InstanzeID, boolean $Value);
```
 De-/Aktiviert den Vollbildmodus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIGUI_ShowNotification(integer $InstanzeID, string $Title, string $Message, string $Image, integer $Timeout);
```
 Erzeugt eine Benachrichtigung.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIGUI_ActivateWindow(integer $InstanzeID, string $Window);
```
 Aktiviert ein Fenster.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIGUI_RequestState(integer $InstanzeID, string $Ident);
```
 Frage einen Wert ab.  
 Es ist der Ident der Statusvariable zu übergeben:  
    "currentwindow",  
    "currentcontrol",  
    "skin",  
    "fullscreen"  
    
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  
 ---

 **Kodi Input (KodiDeviceInput):**  
 
```php
boolean KODIINPUT_Up(integer $InstanzeID);
```
 Sendet den Tastendruck 'hoch'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Down(integer $InstanzeID);
```
 Sendet den Tastendruck 'runter'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Left(integer $InstanzeID);
```
 Sendet den Tastendruck 'links'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Right(integer $InstanzeID);
```
 Sendet den Tastendruck 'rechts'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_ContextMenu(integer $InstanzeID);
```
 Sendet den Tastendruck 'Context-Menü'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Home(integer $InstanzeID);
```
 Sendet den Tastendruck 'Home'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Info(integer $InstanzeID);
```
 Sendet den Tastendruck 'Info'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_Select(integer $InstanzeID);
```
 Sendet den Tastendruck 'auswählen'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_ShowOSD(integer $InstanzeID);
```
 Sendet den Tastendruck 'OSD anzeigen'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_ShowCodec(integer $InstanzeID);
```
 Sendet den Tastendruck 'Codec anzeigen'.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIINPUT_ExecuteAction(integer $InstanzeID, string $Action);
```
 Sendet die in $Action übegebene Aktion.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  
 $Action kann sein:  
 "left", "right", "up", "down", "pageup", "pagedown", "select", "highlight", "parentdir",
 "parentfolder", "back", "previousmenu", "info", "pause", "stop", "skipnext", "skipprevious",
 "fullscreen", "aspectratio", "stepforward", "stepback", "bigstepforward", "bigstepback",
 "chapterorbigstepforward", "chapterorbigstepback", "osd", "showsubtitles", "nextsubtitle",
 "cyclesubtitle", "codecinfo", "nextpicture", "previouspicture", "zoomout", "zoomin",
 "playlist", "queue", "zoomnormal", "zoomlevel1", "zoomlevel2", "zoomlevel3", "zoomlevel4",
 "zoomlevel5", "zoomlevel6", "zoomlevel7", "zoomlevel8", "zoomlevel9", "nextcalibration",
 "resetcalibration", "analogmove", "analogmovex", "analogmovey", "rotate", "rotateccw",
 "close", "subtitledelayminus", "subtitledelay", "subtitledelayplus", "audiodelayminus", 
 "audiodelay", "audiodelayplus", "subtitleshiftup", "subtitleshiftdown", "subtitlealign",
 "audionextlanguage", "verticalshiftup", "verticalshiftdown", "nextresolution", "audiotoggledigital",
 "number0", "number1", "number2", "number3", "number4", "number5", "number6", "number7",
 "number8", "number9", "osdleft", "osdright", "osdup", "osddown", "osdselect", "osdvalueplus",
 "osdvalueminus", "smallstepback", "fastforward", "rewind", "play", "playpause", "switchplayer",
 "delete", "copy", "move", "mplayerosd", "hidesubmenu", "screenshot", "rename", "togglewatched",
 "scanitem", "reloadkeymaps", "volumeup", "volumedown", "mute", "backspace", "scrollup",
 "scrolldown", "analogfastforward", "analogrewind", "moveitemup", "moveitemdown", "contextmenu",
 "shift", "symbols", "cursorleft", "cursorright", "showtime", "analogseekforward", "analogseekback",
 "showpreset", "nextpreset", "previouspreset", "lockpreset", "randompreset","increasevisrating",
 "decreasevisrating", "showvideomenu", "enter", "increaserating", "decreaserating", "togglefullscreen",
 "nextscene", "previousscene", "nextletter", "prevletter", "jumpsms2", "jumpsms3", "jumpsms4",
 "jumpsms5", "jumpsms6", "jumpsms7", "jumpsms8", "jumpsms9", "filter", "filterclear","filtersms2",
 "filtersms3", "filtersms4", "filtersms5", "filtersms6", "filtersms7", "filtersms8","filtersms9",
 "firstpage", "lastpage", "guiprofile", "red", "green", "yellow", "blue", "increasepar",
 "decreasepar", "volampup", "volampdown", "volumeamplification", "createbookmark","createepisodebookmark",
 "settingsreset", "settingslevelchange", "stereomode", "nextstereomode","previousstereomode",
 "togglestereomode", "stereomodetomono", "channelup", "channeldown","previouschannelgroup",
 "nextchannelgroup", "playpvr", "playpvrtv", "playpvrradio", "record", "leftclick", "rightclick",
 "middleclick", "doubleclick", "longclick", "wheelup", "wheeldown", "mousedrag", "mousemove",
 "tap", "longpress", "pangesture", "zoomgesture", "rotategesture", "swipeleft", "swiperight",
 "swipeup", "swipedown", "error", "noop"

```php
boolean KODIINPUT_SendText(integer $InstanzeID, string $Text, boolean $Done);
```
 Sendet den in $Text übegebenen Text an Kodi.  
 Mit $Done = true kann die Eingabe beendet werden.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

---

 **Kodi Playerstatus (KodiDevicePlayer):**  

 ```php
boolean KODIPLAYER_GetItem(integer $InstanzeID);
```  
 Holt die Daten des aktuellen wiedergegebenen Items, und gibt Diese als Array zurück.  
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| channel                   | string    |                               |
| channeltype               | string    |                               |
| file                      | string    |                               |
| sorttitle                 | string    |                               |
| productioncode            | string    |                               |
| cast                      | array     |                               |
| votes                     | string    |                               |
| duration                  | integer   |                               |
| trailer                   | string    |                               |
| albumid                   | integer   |                               |
| musicbrainzartistid       | string    |                               |
| mpaa                      | string    |                               |
| albumlabel                | string    |                               |
| originaltitle             | string    |                               |
| writer                    | string[]  |                               |
| albumartistid             | integer[] |                               |
| episode                   | integer   |                               |
| firstaired                | string    |                               |
| showtitle                 | string    |                               |
| country                   | string[]  ]                               |
| mood                      | string[]  |                               |
| set                       | string    |                               |
| musicbrainztrackid        | string    |                               |
| tag                       | string[]  |                               |
| lyrics                    | string    |                               |
| top250                    | integer   |                               |
| comment                   | string    |                               |
| premiered                 | string    |                               |
| showlink                  | string[]  |                               |
| style                     | string[]  |                               |
| album                     | string    |                               |
| tvshowid                  | integer   |                               |
| season                    | integer   |                               |
| theme                     | string[]  |                               |
| description               | string    |                               |
| setid                     | integer   |                               |
| track                     | integer   |                               |
| tagline                   | string    |                               |
| plotoutline               | string    |                               |
| watchedepisodes           | integer   |                               |
| disc                      | integer   |                               |
| albumartist               | string[]  |                               |
| studio                    | string[]  |                               |
| uniqueid                  | array     |                               |
| episodeguide              | string    |                               |
| imdbnumber                | string    |                               |
| dateadded                 | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| streamdetails             | array     |                               |
| director                  | string[]  |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| displayartist             | string    | Künstler                      |
| artist                    | string[]  | Array der Künstler            |
| genreid                   | integer[] | Array der Genre IDs           |
| musicbrainzalbumartistid  | string    | Music Brainz AlbumArtistID    |
| year                      | integer   | Erscheinungsjahr              |
| rating                    | integer   | Bewertung                     |
| artistid                  | integer[] | Array der Künstler IDs        |
| title                     | string    | Titel der Datei               |
| musicbrainzalbumid        | string    | Music Brainz AlbumID          |
| genre                     | string[]  | Array der Genres              |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |
| hidden                    | boolean   |                               |
| locked                    | boolean   |                               |
| channelnumber             | integer   |                               |
| endtime                   | string    |                               |
| starttime                 | string    |                               |


 ```php
boolean KODIPLAYER_GoToNext(integer $InstanzeID);
```  
 Springt zum nächsten Item in der Wiedergabeliste.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_GoToPrevious(integer $InstanzeID);
```  
 Springt zum vorherigen Item in der Wiedergabeliste.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_GoToTrack(integer $InstanzeID, integer $Value);
```  
 Springt auf ein bestimmtes Item in der Wiedergabeliste.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadAlbum(integer $InstanzeID, integer $AlbumId);
```  
 Lädt ein Album und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadArtist(integer $InstanzeID, integer $ArtistId);
```  
 Lädt alle Itemes eines Artist und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadDirectory(integer $InstanzeID, string $Directory);
```  
 Lädt alle Itemes eines Verzeichnisses und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadDirectoryRecursive(integer $InstanzeID, string $Directory);
```  
 Lädt alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadEpisode(integer $InstanzeID, integer $EpisodeId);
```  
 Lädt eine Episode und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadFile(integer $InstanzeID, string $File);
```  
 Lädt eine Datei und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadGenre(integer $InstanzeID, integer $GenreId);
```  
 Lädt eine komplettes Genre und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadMovie(integer $InstanzeID, integer $MovieId);
```  
 Lädt ein Film und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadMusicVideo(integer $InstanzeID, integer $MusicvideoId);
```  
 Lädt ein Musicvideo und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODPLAYER_LoadPlaylist(integer $InstanzeID);
```  
 Lädt die Playlist des Players und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_LoadSong(integer $InstanzeID, integer $SongId);
```  
 Lädt ein Songs und startet die Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_Pause(integer $InstanzeID);
```  
 Pausiert die Wiedergabe des aktuellen Items.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_Play(integer $InstanzeID);
```  
 Starte die Wiedergabe des aktuelle pausierten Items.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetAudioStream(integer $InstanzeID, integer $Value);
```  
 Aktiviert den Audiostream welcher als Index in $Value übergeben wurde.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetPartymode(integer $InstanzeID, boolean $Value);
```  
 Aktiviert (TRUE) oder deaktiviert (FALSE) den Partymodus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetPosition(integer $InstanzeID, integer $Value);
```  
 Springt auf eine absolute Position innerhalb der aktuellen Wiedergabe.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetRepeat(integer $InstanzeID, integer $Value);
```  
 Setzten den Wiederholungsmodus.  
 $Value darf folgende Werte enthalten:  
  [0=aus, 1=Titel, 2=Alle]  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetShuffle(integer $InstanzeID, boolean $Value);
```  
 Aktiviert (TRUE) oder deaktiviert (FALSE) den Zufallsmodus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetSpeed(integer $InstanzeID, integer $Value);
```  
 Setzten die Abspielgeschwindigkeit.  
 $Value darf folgende Werte enthalten:  
  [-32, -16, -8, -4, -2, 0, 1, 2, 4, 8, 16, 32]  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_SetSubtitle(integer $InstanzeID, integer $Value);
```  
 Aktiviert einen  Untertitel welcher als Index in $Value übergeben wurde.  
 Wird -1 übergeben, wird der Untertitel deaktiviert.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYER_Stop(integer $InstanzeID);
```  
 Stoppt die Wiedergabe des aktuellen Items.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

---

 **Kodi Playlist (KodiDevicePlaylist):**  

Hinweis: Ein mischen von verschiednen Medien (Audio, Video, Bilder) ist nicht möglich.  

 ```php
boolean KODIPLAYLIST_AddAlbum(integer $InstanzeID, integer $AlbumId);
```  
 Fügt der Playliste ein Album hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddArtist(integer $InstanzeID, integer $ArtistId);
```  
 Fügt der Playliste alle Itemes eines Artist hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddDirectory(integer $InstanzeID, string $Directory);
```  
 Fügt der Playliste alle Itemes eines Verzeichnisses hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddDirectoryRecursive(integer $InstanzeID, string $Directory);
```  
 Fügt der Playliste alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddEpisode(integer $InstanzeID, integer $EpisodeId);
```  
 Fügt der Playliste eine Episode hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddFile(integer $InstanzeID, string $File);
```  
 Fügt der Playliste eine Datei hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddGenre(integer $InstanzeID, integer $GenreId);
```  
 Fügt der Playliste eine komplettes Genre hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddMovie(integer $InstanzeID, integer $MovieId);
```  
 Fügt der Playliste ein Film hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddMusicVideo(integer $InstanzeID, integer $MusicvideoId);
```  
 Fügt der Playliste ein Musicvideo hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_AddSong(integer $InstanzeID, integer $SongId);
```  
 Fügt der Playliste ein Song hinzu.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_Clear(integer $InstanzeID);
```  
 Leert die Playlist.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_Get(integer $InstanzeID);
```  
 Gibt alle Einträge Einträge der Playlist als Array zurück.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| title                     | string    | Titel der Datei               |
| artist                    | string[]  | Array der Künstler            |
| albumartist               | string[]  |                               |
| genre                     | string[]  | Array der Genres              |
| year                      | integer   | Erscheinungsjahr              |
| album                     | string    |                               |
| track                     | integer   |                               |
| duration                  | integer   |                               |
| plot                      | string    |                               |
| runtime                   | integer   |                               |
| season                    | integer   |                               |
| episode                   | integer   |                               |
| showtitle                 | string    |                               |
| thumbnail                 | string    | Pfad zum Cover                |
| file                      | string    |                               |
| disc                      | integer   |                               |
| albumlabel                | string    |                               |

 ```php
boolean KODIPLAYLIST_InsertAlbum(integer $InstanzeID, integer $AlbumId, integer $Position);
```  
 Fügt in der Playliste ein Album ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertArtist(integer $InstanzeID, integer $ArtistId, integer $Position);
```  
 Fügt in der Playliste alle Itemes eines Artist ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertDirectory(integer $InstanzeID, string $Directory, integer $Position);
```  
 Fügt in der Playliste alle Itemes eines Verzeichnisses ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertDirectoryRecursive(integer $InstanzeID, string $Directory, integer $Position);
```  
 Fügt in der Playliste alle Itemes eines Verzeichnisses, sowie dessen Unterverzeichnisse, ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertEpisode(integer $InstanzeID, integer $EpisodeId, integer $Position);
```  
 Fügt in der Playliste eine Episode ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertFile(integer $InstanzeID, string $File, integer $Position);
```  
 Fügt in der Playliste eine Datei ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertGenre(integer $InstanzeID, integer $GenreId, integer $Position);
```  
 Fügt in der Playliste eine komplettes Genre ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertMovie(integer $InstanzeID, integer $MovieId, integer $Position);
```  
 Fügt in der Playliste ein Film ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertMusicVideo(integer $InstanzeID, integer $MusicvideoId, integer $Position);
```  
 Fügt in der Playliste ein Musicvideo ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_InsertSong(integer $InstanzeID, integer $SongId, integer $Position);
```  
 Fügt in der Playliste ein Song ein.  
 Alle anderen Einträge werden automatisch nach hinten verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_Remove(integer $InstanzeID, integer $Position);
```  
 Entfernt einen Eintrag aus der Playlist.  
 Alle anderen Einträge werden automatisch nach vorne verschoben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODIPLAYLIST_Swap(integer $InstanzeID, integer $Position1, integer $Position2);
```  
 Tauscht zwei Einträge innerhalb der Playlist.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ---

 **Kodi PVR (KodiDevicePVR):**  

```php
boolean KODIPVR_Scan();
```
 Startet einen Suchlauf.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIPVR_Record(integer $InstanzeID, boolean $Record, string $Channel);
```
 Startet/Beendet eine Aufnahme.  
 Mit $Record TRUE für starten, FALSE zum stoppen.  
 Mit $Channel wird der Kanalname übergeben.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
array|boolean KODIPVR_GetBroadcasts(integer $InstanzeID, integer $ChannelId);
```
 Liefert die Sendungen des in $ChannelId übergeben Kanals als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 
| Index                | Typ       | Beschreibung                      |
|:--------------------:|:---------:|:---------------------------------:|
| broadcastid          | integer   |                                   |
| endtime              | string    |                                   |
| episodename          | string    |                                   |
| episodenum           | integer   |                                   |
| episodepart          | integer   |                                   |
| firstaired           | string    |                                   |
| genre                | string    |                                   |
| hastimer             | boolean   |                                   |
| isactive             | boolean   |                                   |
| parentalrating       | integer   |                                   |
| plot                 | string    |                                   |
| plotoutline          | string    |                                   |
| progress             | integer   |                                   |
| progresspercentage   | float     |                                   |
| rating               | integer   |                                   |
| runtime              | integer   |                                   |
| starttime            | string    |                                   |
| thumbnail            | string    |                                   |
| title                | string    |                                   |
| wasactive            | boolean   |                                   |
     
```php
array|boolean KODIPVR_GetBroadcastDetails(integer $InstanzeID, integer $BroadcastId);
```
 Liefert die Eigenschaften der Sendungen welche mit $BroadcastId übergeben wurde als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 Es gilt die Tabelle von KODIPVR_GetBroadcasts. 
 
```php
array|boolean KODIPVR_GetChannels(integer $InstanzeID, string $ChannelTyp);
```
 Liest alle Kanäle vom Typ $ChannelTyp aus und liefert die Eigenschaften als Array.  
 $ChannelTyp kann 'tv' oder 'radio' sein.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  

| Index                    | Typ      | Beschreibung                  |
|:------------------------:|:--------:|:-----------------------------:|
| channeltype              | string   |                               |
| thumbnail                | string   |                               |
| channel                  | string   |                               |
| hidden                   | boolean  |                               |
| channelid                | integer  |                               |
| locked                   | boolean  |                               |
| lastplayed               | string   |                               |

```php
array|boolean KODIPVR_GetChannelDetails(integer $InstanzeID, integer $ChannelId);
```
 Liefert die Eigenschaften des in $ChannelId übergeben Kanals als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 Es gilt die Tabelle von KODIAPP_GetChannels. 

```php
array|boolean KODIPVR_GetChannelGroups(integer $InstanzeID, string $ChannelTyp);
```
 Liest alle Kanalgruppen vom Typ $ChannelTyp aus und liefert die Eigenschaften als Array.  
 $ChannelTyp kann 'tv' oder 'radio' sein.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  


| Index                    | Typ      | Beschreibung                  |
|:------------------------:|:--------:|:-----------------------------:|
| channeltype              | string   |                               |
| channelgroupid           | integer  |                              |
    
```php
array|boolean KODIPVR_GetChannelGroupDetails(integer $InstanzeID, integer $ChannelGroupdId);
```
 Liefert die Eigenschaften der in $ChannelGroupdId übergeben Kanalgruppe als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 Es gilt die Tabelle von KODIAPP_GetChannelGroups.  

```php
array|boolean KODIPVR_GetRecordings(integer $InstanzeID);
```
 Liefert alle Aufnahmen als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| art                       | array     |                               |
| channel                   | string    |                               |
| directory                 | string    |                               |
| endtime                   | string    |                               |
| file                      | string    |                               |
| genre                     | string[]  | Array der Genres              |
| icon                      | string    |                               |
| lifetime                  | integer   |                               |
| playcount                 | integer   |                               |
| plot                      | string    |                               |
| plotoutline               | string    |                               |
| recordingid               | integer   |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| starttime                 | string    |                               |
| streamurl                 | string    |                               |
| title                     | string    |                               |
 
```php
array|boolean KODIPVR_GetRecordingDetails(integer $InstanzeID, integer $RecordingId);
```
 Liefert die Eigenschaften der in $RecordingId übergeben Aufnahme als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 Es gilt die Tabelle von KODIPVR_GetRecordings. 

```php
array|boolean KODIPVR_GetTimers(integer $InstanzeID);
```
 Liefert alle Aufnahmentimer als Array.
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| channelid                 | integer   |                               |
| directory                 | string    |                               |
| endmargin                 | integer   |                               |
| endtime                   | string    |                               |
| file                      | string    |                               |
| firstday                  | string    |                               |
| isradio                   | boolean   |                               |
| lifetime                  | integer   |                               |
| priority                  | integer   |                               |
| repeating                 | boolean   |                               |
| runtime                   | integer   |                               |
| startmargin               | integer   |                               |
| starttime                 | string    |                               |
| state                     | array     |                               |
| summary                   | string    |                               |
| timerid                   | integer   |                               |
| title                     | string    |                               |
| weekdays                  | array     |                               |

```php
array|boolean KODIPVR_GetTimerDetails(integer $InstanzeID, integer $TimerId);
```
 Liefert die Eigenschaften des in $TimerId übergeben Aufnahmetimers als Array.  
 Rückgabewert ist ein Array bei erfolgreicher Ausführung, sonst FALSE.  
 Es gilt die Tabelle von KODIPVR_GetTimers. 

---

 **Kodi System (KodiDeviceSystem):**  

 ```php
boolean KODISYS_Power(integer $InstanzeID, boolean $Value);
```
 Schaltet Kodi ein (TRUE) oder aus (FALSE).  
 Einschalten erfolgt per hinterlegten PHP-Script in der Instanz-Konfiguration.  
 Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_WakeUp(integer $InstanzeID);
```
 Startet das hinterlegte Einschalt-Script um die Kodi-Hardware einzuschalten.  
 Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_Shutdown(integer $InstanzeID);
```
 Führt einen Shutdown auf Betriebssystemebene aus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_Hibernate(integer $InstanzeID);
```
 Führt einen Hibernate auf Betriebssystemebene aus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_Suspend(integer $InstanzeID);
```
 Führt einen Suspend auf Betriebssystemebene aus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_Reboot(integer $InstanzeID);
```
 Führt einen Reboot auf Betriebssystemebene aus.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

 ```php
boolean KODISYS_EjectOpticalDrive(integer $InstanzeID);
```
 Öffnet das Optische Laufwerk.  
 Ein Schließen ist häufig nicht möglich!  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODISYS_RequestState(integer $InstanzeID, string $Ident);
```
 Frage einen Wert ab.  
 Es können hier nur Fähigkeiten angefragt werden.  
 Diese verstecken automatisch die Aktionsvariablen welche nicht verwendet werden können.
 $Ident kann "canshutdown", "canhibernate", "cansuspend" oder "canreboot" sein.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

---

 **Kodi VideoLibrary (KodiDeviceVideoLibrary):**  

```php
boolean KODIVIDEOLIB_Scan(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
boolean KODIVIDEOLIB_Clean(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

```php
array|boolean KODIVIDEOLIB_GetEpisodeDetails(integer $InstanzeID, integer $EpisodeId);
```
 Liest die Eigenschaften einer Episode aus.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| cast                      | array     |                               |
| productioncode            | string    |                               |
| rating                    | integer   | Bewertung                     |
| votes                     | string    |                               |
| episode                   | integer   |                               |
| showtitle                 | string    |                               |
| episodeid                 | integer   |                               |
| tvshowid                  | integer   |                               |
| season                    | integer   |                               |
| firstaired                | string    |                               |
| uniqueid                  | array     |                               |
| originaltitle             | string    |                               |
| writer                    | string[]  |                               |
| streamdetails             | array     |                               |
| director                  | string[]  |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| dateadded                 | string    |                               |
| file                      | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| title                     | string    | Titel der Datei               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIVIDEOLIB_GetEpisodes(integer $InstanzeID);
```
 Liest die Eigenschaften aller Episoden aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetEpisodeDetails.  

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Episoden aus, welche zuletzt zur Datenbank hinzugefügt wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetEpisodeDetails.  

```php
array|boolean KODIVIDEOLIB_GetGenres(integer $InstanzeID);
```
 Liest die Eigenschaften aller bekannten Genres aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 
| Index     | Typ     | Beschreibung    |
|:---------:|:-------:|:---------------:|
| genreid   | integer | ID des Genres   |
| fanart    | string  | Pfad zum Fanart |
| thumbnail | string  | Pfad zum Cover  |

```php
array|boolean KODIVIDEOLIB_GetMovieDetails(integer $InstanzeID, integer $MovieId);
```
 Liest die Eigenschaften eines Film aus.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| plotoutline               | string    |                               |
| sorttitle                 | string    |                               |
| movieid                   | integer   |                               |
| cast                      | array     |                               |
| votes                     | string    |                               |
| showlink                  | string[]  |                               |
| top250                    | integer   |                               |
| trailer                   | string    |                               |
| year                      | integer   | Erscheinungsjahr              |
| rating                    | integer   | Bewertung                     |
| year                      | integer   | Erscheinungsjahr              |
| country                   | string[]  ]                               |
| studio                    | string[]  |                               |
| set                       | string    |                               |
| genre                     | string[]  | Array der Genres              |
| mpaa                      | string    |                               |
| setid                     | integer   |                               |
| rating                    | integer   | Bewertung                     |
| tag                       | string[]  |                               |
| tagline                   | string    |                               |
| writer                    | string[]  |                               |
| originaltitle             | string    |                               |
| imdbnumber                | string    |                               |
| streamdetails             | array     |                               |
| director                  | string[]  |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| dateadded                 | string    |                               |
| file                      | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| title                     | string    | Titel der Datei               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |


```php
array|boolean KODIVIDEOLIB_GetMovies(integer $InstanzeID);
```
 Liest die Eigenschaften aller Filme aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieDetails.  

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedMovies(integer $InstanzeID);
```
 Liest die Eigenschaften der Filme aus, welche zuletzt zur Datenbank hinzugefügt wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieDetails.  

```php
array|boolean KODIVIDEOLIB_GetMovieSetDetails(integer $InstanzeID, integer $SetId);
```
 Liest die Eigenschaften eines Film-Sets aus.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                      |
|:-------------------------:|:---------:|:---------------------------------:|
| movies                    | array     | Ein Array mit allen Film-Objekten |
| setid                     | integer   |                                  |
| title                     | string    | Titel                             |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIVIDEOLIB_GetMovieSets(integer $InstanzeID);
```
 Liest die Eigenschaften aller Film-Sets aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieSetDetails.  

```php
array|boolean KODIVIDEOLIB_GetMusicVideoDetails(integer $InstanzeID, integer $MusicVideoId);
```
 Liest die Eigenschaften eines Musikvideos aus.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| genre                     | string[]  | Array der Genres              |
| artist                    | string[]  | Array der Künstler            |
| musicvideoid              | integer   |                               |
| tag                       | string[]  |                               |
| album                     | string    |                               |
| track                     | integer   |                               |
| studio                    | string[]  |                               |
| year                      | integer   | Erscheinungsjahr              |
| streamdetails             | array     |                               |
| director                  | string[]  |                               |
| resume                    | array     |                               |
| runtime                   | integer   |                               |
| dateadded                 | string    |                               |
| file                      | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| title                     | string    | Titel der Datei               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIVIDEOLIB_GetMusicVideos(integer $InstanzeID);
```
 Liest die Eigenschaften aller Musikvideos aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetMusicVideoDetails.  

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedMusicVideos(integer $InstanzeID);
```
 Liest die Eigenschaften der Musikvideos aus, welche zuletzt zur Datenbank hinzugefügt wurden.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetMusicVideoDetails.  

```php
array|boolean KODIVIDEOLIB_GetSeasons(integer $InstanzeID, integer $TvShowId);
```
 Liest die Eigenschaften Alles Seasons eine TV-Serie ($TvShowId) aus.
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| showtitle                 | string    |                               |
| watchedepisodes           | integer   |                               |
| tvshowid                  | integer   |                               |
| episode                   | integer   |                               |
| season                    | integer   |                               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIVIDEOLIB_GetTVShowDetails(integer $InstanzeID, integer $TvShowId);
```
 Liest die Eigenschaften eines TV-Serie aus.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  

| Index                     | Typ       | Beschreibung                  |
|:-------------------------:|:---------:|:-----------------------------:|
| sorttitle                 | string    |                               |
| mpaa                      | string    |                               |
| premiered                 | string    |                               |
| year                      | integer   | Erscheinungsjahr              |
| episode                   | integer   |                               |
| watchedepisodes           | integer   |                               |
| votes                     | string    |                               |
| rating                    | integer   | Bewertung                     |
| tvshowid                  | integer   |                               |
| studio                    | string[]  |                               |
| season                    | integer   |                               |
| genre                     | string[]  | Array der Genres              |
| cast                      | array     |                               |
| episodeguide              | string    |                               |
| tag                       | string[]  |                               |
| originaltitle             | string    |                               |
| imdbnumber                | string    |                               |
| dateadded                 | string    |                               |
| file                      | string    |                               |
| lastplayed                | string    |                               |
| plot                      | string    |                               |
| title                     | string    | Titel der Datei               |
| art                       | array     |                               |
| playcount                 | integer   | Anzahl der Wiedergaben        |
| fanart                    | string    | Pfad zum Fanart               |
| thumbnail                 | string    | Pfad zum Cover                |

```php
array|boolean KODIVIDEOLIB_GetTVShows(integer $InstanzeID);
```
 Liest die Eigenschaften aller TV-Serien.  
 Rückgabewert ist ein assoziertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurüchgegeben.  
 Es gilt die Tabelle von KODIVIDEOLIB_GetTVShowDetails.  

```php
boolean KODIVIDEOLIB_Export(integer $InstanzeID, string $Path, boolean $Overwrite, boolean $includeImages);
```
 Exportiert die Audio Datenbank.  
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.  

---  

**Kodi Splitter (KodiSplitter):**  

TODO  

## 8. Parameter / Modul-Infos

GUIDs der Instanzen (z.B. wenn Instanz per PHP angelegt werden soll):  

| Instanz                | GUID                                   |
| :--------------------: | :------------------------------------: |
| KodiDeviceAddons       | {0731DD94-99E6-43D8-9BE3-2854B0C6EF24} |
| KodiDeviceApplication  | {3AF936C4-9B31-48EC-84D8-A30F0BEF104C} |
| KodiDeviceAudioLibrary | {AA078FB4-30C1-4EF1-A2DE-5F957F58BDDC} |
| KodiDeviceFavourites   | {DA2C90A2-3863-4454-9B07-FBD083420E10} |
| KodiDeviceFiles        | {54827867-BB3B-4ACC-A453-7A8D4DC78130} |
| KodiDeviceGUI          | {E15F2C11-0B28-4CFB-AEE6-463BD313A964} |
| KodiDeviceInput        | {9F3BE8BB-4610-49F4-A41A-40E14F641F43} |
| KodiDevicePVR          | {9D73D46E-7B80-4814-A7B2-31768DC6AB7E} |
| KodiDevicePlayer       | {BA014AD9-9568-4F12-BE31-17D37BFED06D} |
| KodiDeviceSystem       | {03E18A60-02FD-45E8-8A2C-1F8E247C92D0} |
| KodiDeviceVideoLibrary | {07943DF4-FAB9-454F-AA9E-702A5F9C9D57} |
| KodiConfigurator       | {7B4F8B62-7AB4-4877-AD60-F3B294DDB43E} |
| KodiSplitter           | {D2F106B5-4473-4C19-A48F-812E8BAA316C} |

Eigenschaften von KodiDeviceAddons:  

| Eigenschaft     | Typ     | Standardwert | Funktion                                      |
| :-------------: | :-----: | :----------: | :-------------------------------------------: |
| showAddonlist   | boolean | true         | HTML-Tabelle mit den Addons erzeugen          |
| Addonlistconfig | integer | auto         | Script mit den Style für die HTML-Tabelle     |
| ThumbSize       | integer | 100          | Breite der Thumbnails                         |
  

Eigenschaften von KodiDeviceApplication:  

| Eigenschaft | Typ     | Standardwert | Funktion                             |
| :---------: | :-----: | :----------: | :----------------------------------: |
| showName    | boolean | true         | Statusvariable für Name verwenden    |
| showVersion | boolean | true         | Statusvariable für Version verwenden |
| showExit    | boolean | true         | Aktions-Variable für beenden anlegen |

Eigenschaften von KodiDeviceAudioLibrary:  

| Eigenschaft | Typ     | Standardwert | Funktion                                             |
| :---------: | :-----: | :----------: | :--------------------------------------------------: |
| showScan    | boolean | true         | Statusvariable für DB-Scanner verwenden              |
| showDoScan  | boolean | true         | Statusvariable für DB-Bereinigung verwenden          |
| showClean   | boolean | true         | Aktions-Variable zum starten des Scan anlegen        |
| showDoClean | boolean | true         | Aktions-Variable zum starten der Bereinigung anlegen |

 Eigenschaften von KodiDeviceFavourites:  

| Eigenschaft    | Typ     | Standardwert | Funktion                                      |
| :------------: | :-----: | :----------: | :-------------------------------------------: |
| showFavlist    | boolean | true         | HTML-Tabelle mit den Favoriten erzeugen       |
| Favlistconfig  | integer | auto         | Script mit den Style für die HTML-Tabelle     |
| ThumbSize      | integer | 100          | Breite der Thumbnails                         |

 Eigenschaften von KodiDeviceFiles:  

keine  

 Eigenschaften von KodiDeviceGUI:  

| Eigenschaft        | Typ     | Standardwert | Funktion                                        |
| :----------------: | :-----: | :----------: | :---------------------------------------------: |
| showCurrentWindow  | boolean | true         | Statusvariable für aktuelles Fenster verwenden  |
| showCurrentControl | boolean | true         | Statusvariable für aktuelle Steuerung verwenden |
| showSkin           | boolean | true         | Statusvariable für Skin verwenden               |
| showFullscreen     | boolean | true         | Statusvariable für Vollbildmodus verwenden      |
| showScreensaver    | boolean | true         | Statusvariable für Bildschirmschoner verwenden  |

 Eigenschaften von KodiDeviceInput: 

| Eigenschaft           | Typ     | Standardwert | Funktion                                 |
| :-------------------: | :-----: | :----------: | :--------------------------------------: |
| showSVGRemote         | boolean | true         | SVG-Remote anzeigen                      |
| RemoteId              | integer | 1            | 1=vertikal, 2=horizontal                 |
| showNavigationButtons | boolean | true         | Aktions-Variable zum navigieren anzeigen |
| showControlButtons    | boolean | true         | Aktions-Variable zum steuern anzeigen    |
| showInputRequested    | boolean | true         | Status-Variable wenn Eingaben nötig sind |

 Eigenschaften von KodiDevicePVR:  

| Eigenschaft            | Typ     | Standardwert | Funktion                                                 |
| :--------------------: | :-----: | :----------: | :------------------------------------------------------: |
| showIsAvailable        | boolean | true         | Status-Variable PVR-Verfügbarkeit anzeigen               |
| showIsRecording        | boolean | true         | Status-Variable Aufzeichnung aktiv anzeigen              |
| showDoRecording        | boolean | true         | Aktions-Variable zum steuern einer Aufzeichnung anzeigen |
| showIsScanning         | boolean | true         | Status-Variable für sktive Kanalsuche anzeigen           |
| showTVChannellist      | boolean | true         | HTML-Tabelle mit den TV Kanälen erzeugen                 |
| showMaxTVChannels      | integer | 20           | Anzahl der darzustellenden TV Kanäle                     |
| TVChannellistconfig    | integer | auto         | Script mit den Style für die HTML-Tabelle                |
| TVThumbSize            | integer | 100          | Breite der Senderlogos                                   |
| showRadioChannellist   | boolean | true         | HTML-Tabelle mit den Radio Kanälen erzeugen              |
| showMaxRadioChannels   | integer | 20           | Anzahl der darzustellenden Radio Kanäle                  |
| RadioChannellistconfig | integer | auto         | Script mit den Style für die HTML-Tabelle                |
| RadioThumbSize         | integer | 100          | Breite der Senderlogos                                   |
| showRecordinglist      | boolean | true         | HTML-Tabelle mit den Aufzeichnungen erzeugen             |
| showMaxRecording       | integer | 20           | Anzahl der darzustellenden Aufzeichnungen                |
| Recordinglistconfig    | integer | auto         | Script mit den Style für die HTML-Tabelle                |
| RecordingThumbSize     | integer | 100          | Breite der Thumbnails                                    |

 Eigenschaften von KodiDevicePlayer:  

| Eigenschaft | Typ     | Standardwert | Funktion                                                         |
| :---------: | :-----: | :----------: | :--------------------------------------------------------------: |
| PlayerID    | integer | 0            | Playermodus: 0 = Audio, 1 = Video, 2 = Bilder                    |
| CoverSize   | integer | 300          | Die Höhe vom Cover in Pixel auf welche das 'Cover' skaliert wird |
| CoverTyp    | string  | thumb        | Varianten: 'thumb', 'artist', 'poster', 'banner'                 |

 Eigenschaften von KodiDevicePlaylist:  

| Eigenschaft    | Typ     | Standardwert | Funktion                                      |
| :------------: | :-----: | :----------: | :-------------------------------------------: |
| PlaylistID     | integer | 0            | Playermodus: 0 = Audio, 1 = Video, 2 = Bilder |
| showPlaylist   | boolean | true         | HTML-Tabelle mit der Playlist erzeugen        |
| Playlistconfig | integer | auto         | Script mit den Style für die HTML-Tabelle     |

 Eigenschaften von KodiDeviceSystem:  

| Eigenschaft     | Typ     | Standardwert | Funktion                                                         |
| :-------------: | :-----: | :----------: | :--------------------------------------------------------------: |
| PowerScript     | integer | 0            | Script welches zum einschalten des System ausgeführt werden soll |
| PowerOff        | integer | 0            | Ausschalt-Methode: 0 = OFF, 1 = Hibernate, 2 = Standby           |
| PreSelectScript | integer | 0            | immer 0 nach Appylchanges, Erzeugt eine PowerScript aus Vorlagen |
| MACAddress      | string  |              | MAC-Adresse für PowerScript                                      |

 Eigenschaften von KodiDeviceVideoLibrary:  

| Eigenschaft | Typ     | Standardwert | Funktion                                             |
| :---------: | :-----: | :----------: | :--------------------------------------------------: |
| showScan    | boolean | true         | Statusvariable für DB-Scanner verwenden              |
| showDoScan  | boolean | true         | Statusvariable für DB-Bereinigung verwenden          |
| showClean   | boolean | true         | Aktions-Variable zum starten des Scan anlegen        |
| showDoClean | boolean | true         | Aktions-Variable zum starten der Bereinigung anlegen |

 Eigenschaften von KodiConfigurator:  

keine  

Eigenschaften von KodiSplitter:  

| Eigenschaft | Typ     | Standardwert | Funktion                                                       |
| :---------: | :-----: | :----------: | :------------------------------------------------------------: |
| Open        | boolean | false        | Verbindung herstellen                                          |
| Host        | string  |              | Hostnamen, IP-Adresse von Kodi                                 |
| Port        | integer | 9090         | Ziel-Port in Kodi für die RPC-JSON-API via TCP                 |
| Webport     | integer | 80           | Webfront von Kodi, wird für den download der Cover genutzt     |
| Watchdog    | boolean | false        | Mit Ping prüfen bevor versucht wird eine Verbindung aufzubauen |
| Interval    | integer | 5            | Interval der Ping-Prüfung                                      |

## 9. Tips & Tricks

- ...
- ...
- ...

## 10. Anhang

**Changlog:**

1.0	:  Release  
1.1     :  Diversese Bugfixes  
           Horizontale Remote hizugefügt  
           PVR zeigt TV & Radio-Kanäle sowie Aufzeichnungen an und kann Diese wiedergeben.  
           Rückkanal für HTML-Tabellen hinzugefügt.

## 11. Lizenz  

[CC BY-NC-SA 4.0] (https://creativecommons.org/licenses/by-nc-sa/4.0/)  
