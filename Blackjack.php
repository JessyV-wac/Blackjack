<?php

$deck = gen_deck();
shuffle($deck);
$players_cards = [$deck[0], $deck[1]];

for ($deck_index = 2; true; $deck_index++) {
    cool_display("Vos cartes: \033[01;33m" . implode(", ", $players_cards) . "\033[0m" . PHP_EOL);
    sleep(1);
    $total = check_hand($players_cards);
    sleep(1);
    if (!decide($players_cards, $deck, $deck_index)) {
        bot_play($total, $deck, $deck_index);
    }
}

function gen_deck()
{
    $cards = [];
    $j = 1;
    for ($i = 1; $i <= 52; $i++)
    {
        if ($j < 2) {
            $cards[$i] = "AS";
        } elseif ($j < 11) {
            $cards[$i] = $j;
        } elseif ($j == 11) {
            $cards[$i] = "VALET";
        } elseif ($j == 12) {
            $cards[$i] = "DAME";
        } else {
            $cards[$i] = "ROI";
        }
        if ($i % 4 == 0) {
            $j++;
        }
    }
    return $cards;
}

function check_hand(&$cards, $bot = false, &$bot_total = 0)
{
    $total = 0;
    foreach ($cards as $key => $value) {
        if ($value == "AS") {
            if ($bot) {
                if ($bot_total < 11) {
                    $cards[$key] .= "(11)";
                    $bot_total += 11;
                } else {
                    $cards[$key] .= "(1)";
                    $bot_total += 1;
                }
            } else {
                while ($input = readline("Vous avez un 'AS', choisissez sa valeur: [ 1 / 11 ] ")) {
                    if (trim($input) == "1") {
                        $cards[$key] .= "(1)";
                        $total++;
                        break;
                    } elseif (trim($input) == "11") {
                        $cards[$key] .= "(11)";
                        $total += 11;
                        break;
                    } else {
                        cool_display("Veuillez entrer '1' ou '11'" . PHP_EOL);
                    }
                }
            }
        } elseif (preg_match("/AS\(1+\)/", $value)) {
            preg_match("/1+/", $value, $matches);
            $total += $matches[0];
            $bot_total += $matches[0]; 
        } elseif (preg_match("/VALET|DAME|ROI/", $value)) {
            $total += 10;
            $bot_total += 10;
        } else {
            $total += $value;
            $bot_total += $value;
        }
    }
    if ($bot) {
        is_out($bot_total, true);
    } else {
        is_out($total);
    }
    return $total;
}

function decide(&$players_cards, $deck, $deck_index)
{
    while ($input = readline("Que voulez-vous faire ? [ 'carte' / 'je reste' ] ")) {
        if (trim($input) == "carte") {
            $players_cards[$deck_index] = $deck[$deck_index];
            echo PHP_EOL;
            return 1;
        } elseif (trim($input) == "je reste") {
            echo PHP_EOL;
            return 0;
        } else {
            echo PHP_EOL;
            cool_display("Veuillez entrer 'carte' ou 'je reste'" . PHP_EOL);
        }
    }
}

function bot_play($player_total, $deck, $deck_index)
{
    $bots_cards = [$deck[$deck_index], $deck[$deck_index + 1]];
    $bot_total = 0;
    cool_display("Le croupier pioche deux cartes" . PHP_EOL);
    sleep(1);
    cool_display("Cartes du croupier: \033[01;33m" . implode(", ", $bots_cards) . "\033[0m" . PHP_EOL);
    sleep(1);
    check_hand($bots_cards, true, $bot_total);
    sleep(1);
    echo PHP_EOL;
    
    while ($bot_total < 17) {
        $bot_total = 0;
        $bots_cards[$deck_index] = $deck[$deck_index];
        cool_display("Cartes du croupier: \033[01;33m" . implode(", ", $bots_cards) . "\033[0m" . PHP_EOL);
        sleep(1);
        check_hand($bots_cards, true, $bot_total);
        sleep(1);
        $deck_index++;
        echo PHP_EOL;
    }
    cool_display("Le croupier ne pioche pas" . PHP_EOL . PHP_EOL);
    if ($player_total > $bot_total) {
        exit("Vous avez gagné !" . PHP_EOL);
    } elseif ($player_total == $bot_total) {
        exit("Egalité" . PHP_EOL);
    } else {
        exit("Vous avez perdu" . PHP_EOL);
    }
}

function is_out($total, $bot = false)
{
    if ($bot) {
        cool_display("Le total des cartes du croupier équivaut à \033[01;31m$total\033[0m" . PHP_EOL);
        if ($total > 21) {
            exit("Vous avez gagné !" . PHP_EOL);
        } elseif ($total == 21) {
            exit("BLACKJACK ! Vous avez perdu !" . PHP_EOL);
        }    
    } else {
        cool_display("Le total de vos cartes équivaut à \033[01;31m$total\033[0m" . PHP_EOL);
        if ($total > 21) {
            exit("Vous avez perdu" . PHP_EOL);
        } elseif ($total == 21) {
            exit("BLACKJACK ! Vous avez gagné !" . PHP_EOL);
        }
    }
}

function cool_display($text)
{
    for ($i = 0; $i < strlen($text); $i++) {
        echo $text[$i];
        usleep(25000);
    }
}