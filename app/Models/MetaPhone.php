<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaPhone extends Model
{
    use HasFactory;

    public function removerAcento($txt)
    {
        $keys = array();
        $values = array();
        preg_match_all('/./u', 'áàãâéêíóôõúüÁÀÃÂÉÊÍÓÔÕÚÜ', $keys);
        preg_match_all('/./u', 'aaaaeeiooouuAAAAEEIOOOUU', $values);
        $mapping = array_combine($keys[0], $values[0]);

        return strtr($txt, $mapping);
    }

    public function removerAcentoCompleto($txt)
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $txt);
    }

    public function isVowel($paramChar)
    {
        switch ($paramChar) {
            case 'A':
            case 'E':
            case 'I':
            case 'O':
            case 'U':
                return true;
        }

        return false;
    }

    public function toUpper($paramChar)
    {
        $i = mb_strtoupper($paramChar, 'UTF-8');

        switch ($i) {
                // case 'ç': return 'Ç';
            case 'Á':
            case 'À':
            case 'Ã':
            case 'Â':
            case 'Ä':
                return 'A';
            case 'É':
            case 'È':
            case 'Ẽ':
            case 'Ê':
            case 'Ë':
                return 'E';
            case 'Y':
            case 'Í':
            case 'Ì':
            case 'Ĩ':
            case 'Î':
            case 'Ï':
                return 'I';
            case 'Ó':
            case 'Ò':
            case 'Õ':
            case 'Ô':
            case 'Ö':
                return 'O';
            case 'Ú':
            case 'Ù':
            case 'Ũ':
            case 'Û':
            case 'Ü':
                return 'U';
        }

        return $i;
    }

    public function isWordEdge($char1)
    {
        if (
            $char1 == '' || $char1 == "\0" || $char1 == "\n" || $char1 == ' '
            || $char1 == "\t"
        ) {
            return true;
        }

        return false;
    }

    public function getMetaphone($paramStr)
    {
        $strAnalise = '';

        //Letras que precisam ter a duplicação retirada
        $arrDuplos = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'T', 'V', 'W', 'X', 'Z'];

        // preparação - caixa alta e limpar letras duplicadas (comuns com nomes)
        for ($i = 0; $i < mb_strlen($paramStr, 'UTF-8'); $i++) {
            $char = $this->toUpper(mb_substr($paramStr, $i, 1, 'UTF-8'));

            if ($i == 0) {
                $strAnalise .= $char;
            } else {
                $prevChar = mb_substr($strAnalise, mb_strlen($strAnalise, 'UTF-8') - 1, 1, 'UTF-8');

                if ($prevChar != $char || array_search($char, $arrDuplos) === false) {
                    $strAnalise .= $char;
                }
            }
        }

        // variaveis para o algoritmo
        $metaString = '';
        $tamanho = mb_strlen($strAnalise, 'UTF-8');
        $charAhead1 = '';
        $charAhead2 = '';
        $charLast1 = '';
        $charLast2 = '';
        $charCurrent = '';

        $i = 0;

        while ($i < $tamanho && mb_strlen($metaString, 'UTF-8') < 4) {
            $charCurrent = mb_substr($strAnalise, $i, 1, 'UTF-8');

            switch ($charCurrent) {
                case 'A':
                case 'E':
                case 'I':
                case 'O':
                case 'U':
                    /*
                     * vogais iniciando a palavra ficam. herança do metaphone
                     * original, mas pode-se discutir removê-las no futuro
                     */
                    if ($this->isWordEdge($charLast1)) {
                        $metaString .= $charCurrent;
                    }
                    break;

                case 'L':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');
                    /* lha, lho. */
                    if ($charAhead1 == 'H') {
                        $metaString .= '1';
                    } else {
                        /* como em Louco, aloprado, alado, lampada, etc */
                        if ($this->isVowel($charAhead1) || $this->isWordEdge($charLast1)) {
                            $metaString .= 'L';
                        }
                        /* atualmente ignora L antes de consoantes */
                    }
                    break;

                case 'T':
                case 'P':
                    /*
                     * Casos especiais de nomes estrangeiros ou sintaxe antiga do
                     * português.
                     */
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    if ($charAhead1 == 'H') {
                        /* phone, pharmacia, teophilo */
                        if ($charCurrent == 'P') {
                            $metaString .= 'F';
                        } else {
                            $metaString .= 'T';
                        }

                        $i++;
                        break;
                    }

                    // no break
                case 'B':
                case 'D':
                case 'F':
                case 'J':
                case 'K':
                case 'M':
                case 'V':
                    $metaString .= $charCurrent;
                    break;

                    /* checar consoantes com som confuso e similares */
                case 'G':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    switch ($charAhead1) {
                        case 'H':
                            /*
                             * H sempre complica a vida. Se não for vogal, tratar como
                             * 'G', caso contrário segue o fluxo abaixo.
                             */
                            if (!$this->isVowel(mb_substr($strAnalise, $i + 2, 1, 'UTF-8'))) {
                                $metaString .= 'G';
                                break;
                            }
                            // no break
                        case 'E':
                        case 'I':
                            $metaString .= 'J';
                            break;

                        default:
                            $metaString .= 'G';
                            break;
                    }
                    break;

                case 'R':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    /* como em andar, carro, rato */
                    if ($this->isWordEdge($charLast1) || $this->isWordEdge($charAhead1)) {
                        $metaString .= '2';
                    } elseif ($charAhead1 == 'R') {
                        $metaString .= '2';
                        $i++;
                    } elseif ($this->isVowel($charLast1) && $this->isVowel($charAhead1)) {
                        /* como em arara */
                        $metaString .= 'R';
                        $i++;

                    /* todo o resto, como em arsenico */
                    } else {
                        $metaString .= 'R';
                    }

                    break;

                case 'Z':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    /* termina com, como em algoz */
                    if ($this->isWordEdge($charAhead1)) {
                        $metaString .= 'S';
                    } else {
                        $metaString .= 'Z';
                    }
                    break;

                case 'N':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    /*
                     * no português, todas as palavras terminam com 'M', exceto no
                     * caso de nomes próprios, ou estrangeiros. Para todo caso, tem
                     * som de 'M'
                     */
                    if ($this->isWordEdge($charAhead1)) {
                        $metaString .= 'M';
                    } elseif ($charAhead1 == 'H') {
                        /* aranha, nhoque, manha */
                        $metaString .= '3';
                        $i++;
                    } elseif ($charLast1 != 'N') {
                        /* duplicado... */
                        $metaString .= 'N';
                    }
                    break;

                case 'S':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    /* aSSar */
                    if ($charAhead1 == 'S') {
                        $metaString .= 'S';
                        $charLast1 = $charAhead1;
                        $i++;
                    } elseif ($charAhead1 == 'H') {
                        /*
                        * mais estrangeirismo: sheila, mishel, e compatibilidade sonora
                        * com sobrenomes estrangeiros (japoneses)
                        */
                        $metaString .= 'X';
                        $i++;
                    } elseif ($this->isVowel($charLast1) && $this->isVowel($charAhead1)) {
                        /* como em asa */
                        $metaString .= 'Z';
                    } elseif ($charAhead1 == 'C') {
                        /* special cases = 'SC' */
                        $charAhead2 = mb_substr($strAnalise, $i + 2, 1, 'UTF-8');

                        switch ($charAhead2) {
                            /* aSCEnder, laSCIvia */
                            case 'E':
                            case 'I':
                                $metaString .= 'S';
                                $i += 2;
                                break;

                                /* maSCAvo, aSCO, auSCUltar */
                            case 'A':
                            case 'O':
                            case 'U':
                                $metaString .= 'SK';
                                $i += 2;
                                break;

                                /* estrangeirismo tal como scheila. */
                            case 'H':
                                $metaString .= 'X';
                                $i += 2;
                                break;

                                /* mesclado */
                            default:
                                $metaString .= 'S';
                                $i++;
                                break;
                        }
                    } else {
                        /* pega o resto - deve pegar atrás e sapato */
                        $metaString .= 'S';
                    }
                    break;
                case 'X':
                    /* muitas, mas muitas exceções mesmo... ahh! */
                    $charLast2 = mb_substr($strAnalise, $i - 2, 1, 'UTF-8');
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    /* fax, anticlímax e todos terminados com 'X' */
                    if ($this->isWordEdge($charAhead1)) {
                        /* como em: Felix, Alex */
                        /*
                        * o som destes casos: "KS" para manter compatibilidade com
                        * outra implementação, usar abaixo X Na verdade, para o
                        * computador tanto faz. Se todos usarem o mesmo
                        * significado, o computador sabe q são iguais, não que som
                        * q tem. A discussão está na representação acurada ou não
                        * da fonética.
                        */
                        $metaString .= 'X';
                    } elseif ($charLast1 == 'E') {
                        if ($this->isVowel($charAhead1)) {
                            /*
                            * começados com EX. Exonerar, exército, executar,
                            * exemplo, exame, exílio, exuberar = ^ex + vogal
                            */
                            if ($this->isWordEdge($charLast2)) {
                                /* deixado com o som original dele */
                                $metaString .= 'Z';
                            } else {
                                switch ($charAhead1) {
                                    case 'E':
                                    case 'I':
                                        /* México, mexerica, mexer */
                                        $metaString .= 'X';
                                        $i++;
                                        break;
                                    default:
                                        /*
                                        * Anexar, sexo, convexo, nexo, circunflexo
                                        * sexual inclusive Alex e Alexandre, o que eh
                                        * bom, pois há Aleksandro ou Alex sandro OBS:
                                        * texugo cai aqui.
                                        */
                                        $metaString .= 'KS';
                                        $i++;
                                        break;
                                }
                            }
                            // Ï
                        } elseif ($charAhead1 == 'C') {
                            /* exceção, exceto */
                            $metaString .= 'S';
                            $i++;
                        /*
                        * expatriar, experimentar, extensão, exterminar.
                        * Infelizmente, êxtase cai aqui
                        */
                        } elseif ($charAhead1 == 'P' || $charAhead1 == 'T') {
                            $metaString .= 'S';
                        } else {
                            /* o resto... */
                            $metaString .= 'KS';
                        }
                    } elseif ($this->isVowel($charLast1)) {
                        /*
                         * parece que certas sílabas predecessoras do 'x' como 'ca' em
                         * 'abacaxi' provocam o som de 'CH' no 'x'. com exceção do 'm',
                         * q é mais complexo.
                         */

                        /* faxina. Fax é tratado acima. */
                        switch ($charLast2) {
                            /* encontros vocálicos */
                            case 'A':
                            case 'E':
                            case 'I':
                            case 'O':
                            case 'U': /*
                                    * caixa, trouxe, abaixar, frouxo, guaxo,
                                    * Teixeira
                                    */
                            case 'C': /* coxa, abacaxi */
                            case 'K':
                            case 'G': /* gaxeta */
                            case 'L': /* laxante, lixa, lixo */
                            case 'R': /* roxo, bruxa */
                            case 'X': /* xaxim */
                                $metaString .= 'X';
                                break;

                            default:
                                /*
                                * táxi, axila, axioma, tóxico, fixar, fixo, monóxido,
                                * óxido
                                */
                                /*
                                * maxilar e enquadra máximo aqui tb, embora não seja
                                * correto.
                                */
                                $metaString .= 'KS';
                                break;
                        }
                    } else {
                        /* tudo o mais... enxame, enxada, -- :( */
                        $metaString .= 'X';
                    }
                    break;
                case 'C':
                    /* ca, ce, ci, co, cu */
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    switch ($charAhead1) {
                        case 'E':
                        case 'I':
                            $metaString .= 'S';
                            break;

                        case 'H':
                            /* christiano. */
                            if (mb_substr($strAnalise, $i + 2, 1, 'UTF-8') == 'R') {
                                $metaString .= 'K';
                            } else {
                                /* CHapéu, chuva */
                                $metaString .= 'X';
                            }

                            $i++;

                            break;

                            /*
                         * Jacques - não fazer nada. Deixa o 'Q' cuidar disso ou
                         * palavras com CK, mesma coisa.
                         */
                        case 'Q':
                        case 'K':
                            break;

                        default:
                            $metaString .= 'K';
                            break;
                    }

                    break;

                    /*
                 * Se 'H' começar a palavrar, considerar as vogais que vierem depois
                 */
                case 'H':
                    if ($this->isWordEdge($charLast1)) {
                        $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');
                        if ($this->isVowel($charAhead1)) {
                            $metaString .= $charAhead1;
                            /*
                             * Ex: HOSANA será mapeada para som de 'S', ao invés de
                             * 'Z'. OBS: para voltar à representação de Z, comente a
                             * linha abaixo
                             */
                            $i++;
                        }
                    }

                    break;

                case 'Q':
                    $metaString .= 'K';
                    break;

                case 'W':
                    $charAhead1 = mb_substr($strAnalise, $i + 1, 1, 'UTF-8');

                    if ($this->isVowel($charAhead1)) {
                        $metaString .= 'V';
                    }

                    /*
                     * desconsiderar o W no final das palavras, por ter som de U, ou
                     * ainda seguidos por consoantes, por ter som de U (Newton)
                     *
                     * soluções para www?
                     */
                    break;

                case 'Ç':
                    $metaString .= 'S';
                    break;
            }

            /* next char */
            $i++;

            $charLast1 = $charCurrent;
        }

        return $metaString;
    }

    public function getPhraseMetaphone($phrase)
    {
        $return = '';

        $explode = explode(' ', $phrase);

        foreach ($explode as $word) {
            $return .= $this->getMetaphone($word) . ' ';
        }

        return $return;
    }

    public function nameCase($string, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("de", "da", "dos", "das", "do", "I", "II", "III", "IV", "V", "VI"))
    {
        $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        foreach ($delimiters as $dlnr => $delimiter) {
            $words = explode($delimiter, $string);
            $newwords = array();
            foreach ($words as $wordnr => $word) {
                if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtoupper($word, "UTF-8");
                } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
                    // check exceptions list for any words that should be in upper case
                    $word = mb_strtolower($word, "UTF-8");
                } elseif (!in_array($word, $exceptions)) {
                    // convert to uppercase (non-utf8 only)
                    $word = ucfirst($word);
                }
                array_push($newwords, $word);
            }
            $string = join($delimiter, $newwords);
        }//foreach
        return $string;
    }
}
