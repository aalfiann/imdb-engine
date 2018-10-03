<?php
namespace aalfiann;
    /**
     * This class is a part of imdb-engine project
     *
     * @package    IMDBHelper Class
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/imdb-engine/blob/master/LICENSE.md  MIT License
     */
    class IMDBHelper {
        
        /**
         * Send a request to server
         * 
         * @param url is the url of the server
         * @param output if set to true then will return response in output. Default is false.
         * 
         * @return mixed string or no any return 
         */
        public function sendRequest($url,$output=false){
            $agents = array(
                'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                'Mozilla/5.0 (X11; CrOS x86_64 8172.45.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.64 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
                'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1',
                'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
                'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:62.0) Gecko/20100101 Firefox/62.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:60.0) Gecko/20100101 Firefox/60.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:58.0) Gecko/20100101 Firefox/58.0',
                'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0'
            );
            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";
            $header[] = "Pragma: ";
            
            $req = new ParallelRequest;
            $req->request = $url;
            $req->options = [
                CURLOPT_NOBODY => false,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => $agents[array_rand($agents)],
                CURLOPT_HTTPHEADER => $header
            ];
            if (!empty($this->proxy)) $req->options[CURLOPT_PROXY] = $this->proxy;
            if (!empty($this->proxy) && !empty($this->proxyauth)) $req->options[CURLOPT_PROXYUSERPWD] = $this->proxyauth;
            if ($output==false){
                $this->htmlData = $req->send()->getResponse();
            } else {
                return $req->send()->getResponse();
            }
        }
        
        /**
         * Regex to get meta property
         * 
         * @param property is the value of property attribute
         * 
         * @return string
         */
        public function getProperty($property){
            if (!empty($this->htmlData)){
                preg_match('/<meta property=[\'"]'.$property.'[\'"] content=[\'"](.*?)[\'"].*?>/is', $this->htmlData, $match);
                if (!empty($match) && !empty($match[1])){
                    return trim($match[1]);
                }
            }
            return "";
        }

        /**
         * Regex to get meta value
         * 
         * @param name is the value of name attribute
         * 
         * @return string
         */
        public function getMeta($name){
            if (!empty($this->htmlData)){
                preg_match('/<meta name=[\'"]'.$name.'[\'"] content=[\'"](.*?)[\'"].*?>/is', $this->htmlData, $match);
                if (!empty($match) && !empty($match[1])){
                    return trim($match[1]);
                }
            }
            return "";
        }
        
        /**
         * Regex to get span value with specifix attribute
         * 
         * @param attr is the attribute. Ex: class="itemprop".
         * @return string
         */
        public function getSpanValue($attr){
            if (!empty($this->htmlData)){
                preg_match('~<span '.$attr.'>(.*)</span>~Uis',$this->htmlData,$match);
                if (!empty($match) && !empty($match[1])){
                    return trim($match[1]);
                }
            }
            return "";
        }

        /**
         * Regex to get meta title value
         * 
         * @return string
         */
        public function getTitle(){
            if (!empty($this->htmlData)){
                preg_match('~<title>(.*)</title>~Uis',$this->htmlData,$match);
                if (!empty($match) && !empty($match[1])){
                    return trim(str_replace(' - IMDb','',$match[1]));
                }
            }
            return "";
        }

        /**
         * Accept only integer
         * 
         * @param string is the source text
         * 
         * @return string
         */
        public function integerOnly($string) {
    		return preg_replace("/[^0-9]/", "", $string );
        }

        /**
         * Accept only alphanumeric
         * 
         * @param string is the source text
         * 
         * @return string
         */
        public function alphaNumericOnly($string) {
    		return preg_replace("/[^a-zA-Z0-9]/", "", $string );
        }
        
        /**
         * Sanitize the variable
         */
        public function sanitizer(){
            $this->userid = $this->alphaNumericOnly($this->userid);
            $this->start = $this->integerOnly($this->start);
            $this->page = $this->integerOnly($this->page);
            $this->itemsperpage = $this->integerOnly($this->itemsperpage);
        }

        /**
         * Determine if string is contains matched text
         * 
         * @param match is the text to match
         * @param string is the source text
         * 
         * @return bool
         */
        public function isContains($match,$string){
            if(strpos($string,$match) !== false){
                return true;
            }
            return false;
        }

        /**
         * Convert Accents to string
         * 
         * @param string is the text
         * 
         * @return string
         */
        public function convertAccents($string) {
            if (!preg_match('/[\x80-\xff]/', $string)) return $string;
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's'
            );
            $string = strtr($string, $chars);    
            return $string;
        }

        /**
         * Regular expression helper.
         *
         * @param string $sContent The content to search in.
         * @param string $sPattern The regular expression.
         * @param null   $iIndex   The index to return.
         *
         * @return mixed
         * @return bool   If no match was found.
         * @return string If one match was found.
         * @return array  If more than one match was found.
         */
        public function matchRegex($sContent, $sPattern, $iIndex = null) {
            preg_match_all($sPattern, $sContent, $aMatches);
            if ($aMatches === false) return false;
            if ($iIndex !== null && is_int($iIndex)) {
                if (isset($aMatches[$iIndex][0])) return $aMatches[$iIndex][0];
                return false;
            }
            return $aMatches;
        }

        /**
         * @param string $sInput Input (eg. HTML).
         *
         * @return string Cleaned string.
         */
        public function cleanString($sInput) {
            $aSearch  = [
                'Full summary &raquo;',
                'Full synopsis &raquo;',
                'Add summary &raquo;',
                'Add synopsis &raquo;',
                'See more &raquo;',
                'See why on IMDbPro.',
                'See full summary',
                "\n",
                "\r"
            ];
            $aReplace = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ];
            $sInput   = str_replace('</li>', ' | ', $sInput);
            $sInput   = strip_tags($sInput);
            $sInput   = str_replace('&nbsp;', ' ', $sInput);
            $sInput   = str_replace('»',' ',$sInput);
            $sInput   = str_replace(chr( 194 ) . chr( 160 ),' ', $sInput);
            $sInput   = str_replace($aSearch, $aReplace, $sInput);
            $sInput   = html_entity_decode($sInput, ENT_QUOTES | ENT_HTML5);
            $sInput   = preg_replace('/\s+/', ' ', $sInput);
            $sInput   = trim($sInput);
            $sInput   = rtrim($sInput, ' |');
            return ($sInput ? trim($sInput) : '');
        }

    }