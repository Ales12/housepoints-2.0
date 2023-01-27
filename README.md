# housepoints-2.0
Plugin, um die Hauspunkt zu zählen.

## was macht es?
Es ermöglicht die Automatische Zählung der Hauspunkte eines Charakters. Hierbei ließt er die Zugehörigkeit des charakters eines Profilfeldes aus.
Erstelle das Profilfeld als **Auswahlfeld**! Wichtig ist hier, das du als ERSTE Auswahl *keine Auswahl* angibst, so dass alle Charaktere, die nicht mitgerechnet werden, nicht unbeabsichtet reinfallen.

## Datenbank

- Erstellt die Datenbank **housepoints**
- Fügt der Usertabelle die Spalte **user_hp** hinzu
- Fügt der Usergrouptabelle die Spalte **canaddhp** hinzu

## variabeln
- header
  - {$hp_alert_newrequests}{$hp_header}
 
 - member_profile
   -  {$profile_points}

- modcp_nav_users
  - {$housepoints_modcp}

## Pfad zur Übersicht
https://deineadresse.de/misc.php?action=housepoints_overview
  

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

## CSS
```
.hp_flex{
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	align-items: center;
}

.hp_hg{
	margin: 5px 15px;	
	text-align: center;
	width: 35%;
}

.houreglass{
	font-size:50px;
}

.hp_totalscore{
	text-align: center;
font-family: 'Roboto', sans-serif;
font-size: 9px;
text-transform: uppercase;
color: var(--color1);
	letter-spacing: 1px;
}

.overview_hp_hg{
	margin: 2px 20px;	
	text-align: center;
}

.overview_houreglass{
	font-size: 150px;
}

.overview_hp_totalscore{
	text-align: center;
font-family: 'Roboto', sans-serif;
font-size: 12px;
text-transform: uppercase;
color: var(--color1);
	letter-spacing: 1px;
	font-weight: 700;
}

.hp_charas{
	height: 150px;
	overflow: auto;
	padding: 2px 5px;
	box-sizing: border-box;
	scrollbar-width: none !important;
}

.Gryffindor i{
		color: var(--gry);
}

.Hufflepuff i{
		color: var(--huf);
}

.Ravenclaw i{
		color: var(--rav);
}


.Slytherin i{
		color: var(--sly);
}
```

## Übersicht und Punkteanfrage
![Bildtext](https://up.picr.de/45102115dy.png "Punkte Overview")
Hier können User für ihre eigenen Charaktere Punkte anfordern. Diese müssen aber vom Team erst abgesegnet werden, bevor sie dazu oder abgezogen werden.

## Wo werden die Punkte ausgelesen
Die Punkte werden im **Profil** automatisch ausgelesen, können aber ganz normal aus der Usertabelle ausgelesen werden. 

## Stundengläser Header und Overview
ich nutze dafür Fontawesome. 

### hp_header_houses
```
<div class="hp_hg">
<div class="{$house} houreglass"><i class="fa-duotone fa-hourglass-start"></i></div>
<div class="hp_totalscore">{$total_score}</div>
</div>
```

### hp_misc_bit
```
<div class="hp_hg">
<div class="{$house} overview_houreglass"><i class="fa-duotone fa-hourglass-start"></i></div>
<div class="hp_totalscore">{$total_score} Hauspunkte</div>
	<div class="hp_charas">
			{$hp_misc_chara}
	</div>
</div>
```
