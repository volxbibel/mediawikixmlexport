<?php
/*
 * volxbibelwiki
 * Simon Br�chner, 06.02.2007
 */

$buchUebersichtContent = "Die Geschichte von Jesus, wie Matthäus sie aufgeschrieben hat


= Info =
== Verfasser ==
 
Matthäus war von Beruf so einer, der für die Besatzungmacht (Römer) die Steuern und Zollgebühren eintrieb. Das wurde damals durch \"freie Unternehmer\" getan, die feste Beträge abliefern mussten und davon lebten, mehr Kohle einzutreiben als sie ablieferten. Solche Menschen waren damals bei der Bevölkerung natürlich extrem unbeliebt.

Nachdem er Jesus kennengelernt hatte, ging er mit ihm mit. 
Matthäus hat Jesus also noch live miterlebt und ist mit ihm eine Weile umhergezogen.
Sein Buch ist vermutlich im Original so um '''40 bis 60''' Jahre nachdem Jesus geboren wurde aufgeschrieben worden.

Wegen der Ähnlichkeiten zum Markusevangelium vermuten manche, dass es vielleicht doch ein anderer Typ war, der das Matthäusevangelium geschrieben hat.

== Reden ==
Berühmt berüchtigt sind die 5 Vorträge von Jesus, die Matthäus aufgeschrieben hat: 

1. Die Rede auf dem Berg (Kapitel 5-7) 
(am bekanntesten die \"Seligpreisungen\", Versprechungen an arme Schweine und andere Leute, am Anfang)

2. Jesus schickt seine Leute unters Volk (Kapitel 10) 

3. Jesus spricht in Rätseln (Kapitel 13) 

4. Jesus sagt, wo es lang geht (Kapitel 18) und 

5. Jesus wettert und teilt aus (Kapitel 23-25)

== Die Kapitel ==
<div class=\"kapiteluebersicht\">
{| 
| [[Matthus 1]] || [[Matthäus 2]] || [[Matthäus 3]] || [[Matthäus 4]] || [[Matthäus 5]]
|-
| [[Matthäus 6]] || [[Matthäus 7]] || [[Matthäus 8]] || [[Matthäus 9]] || [[Matthäus 10]]
|-
| [[Matthäus 11]] || [[Matthäus 12]] || [[Matthäus 13]] || [[Matthäus 14]] || [[Matthäus 15]]
|-
| [[Matthäus 16]] || [[Matthäus 17]] || [[Matthäus 18]] || [[Matthäus 19]] || [[Matthäus 20]]
|-
| [[Matthäus 21]] || [[Matthäus 22]] || [[Matthäus 23]] || [[Matthäus 24]] || [[Matthäus 25]]
|-
| [[Matthäus 26]] || [[Matthäus 27]] || [[Matthäus 28]]
|}
</div>";


$pattern = '/\[\[([^]]+)\]\]/';

preg_match_all($pattern, $buchUebersichtContent, $result1);
var_dump($result1);
$kapitel = $result1[1]; // Array, kein trim() oder (string)!
echo '<hr />';
echo var_dump($kapitel);
?>