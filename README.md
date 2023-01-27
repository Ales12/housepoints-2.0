# housepoints-2.0
Plugin, um die Hauspunkt zu zählen.

## was macht es?
Es ermöglicht die Automatische Zählung der Hauspunkte eines Charakters. Hierbei ließt er die Zugehörigkeit des charakters eines Profilfeldes aus.
Erstelle das Profilfeld als **Auswahlfeld**! Wichtig ist hier, das du als ERSTE Auswahl *keine Auswahl* angibst, so dass alle Charaktere, die nicht mitgerechnet werden, nicht unbeabsichtet reinfallen.

## Datenbank

- Erstellt die Datenbank **housepoints**
- Fügt der Usertabelle die Spalte **user_hp** hinzu
- Fügt der Usergrouptabelle die Spalte **canaddhp** hinzu

## Einstellungen
![Bildtext](https://up.picr.de/45102104ec.png "Einstellung ohne Zusatzpunkte")
Einstellunge ohne Zusatzpunkte

![Bildtext](https://up.picr.de/45102103qu.png "Einstellung mit Zusatzpunkte")
Einstellungen mit Zusatzpunkte

![Bildtext](https://up.picr.de/45102131aj.png "Gruppeneinstellung")
Unter Sonstige kann man angeben, ob jemand eigene Punkte anfragen kann oder nicht

## Templates
- hp_header
- hp_header_houses
- hp_misc
- hp_misc_bit
- hp_misc_chara
- hp_misc_form
- hp_modcp
- hp_modcp_charas
- hp_modcp_charas_protocol
- hp_newrequest
- hp_own_protocol
- hp_own_protocol_bit
- hp_profile

## Übersicht und Punkteanfrage
![Bildtext](https://up.picr.de/45102115dy.png "Punkte Overview")
Hier können User für ihre eigenen Charaktere Punkte anfordern. Diese müssen aber vom Team erst abgesegnet werden, bevor sie dazu oder abgezogen werden.

## Wo werden die Punkte ausgelesen
Die Punkte werden im **Profil** automatisch ausgelesen, können aber ganz normal aus der Usertabelle ausgelesen werden. 
