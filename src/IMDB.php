<?php
namespace aalfiann;
use Symfony\Component\DomCrawler\Crawler;
    /**
     * This class is a part of imdb-engine project
     *
     * @package    IMDB Class
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/imdb-engine/blob/master/LICENSE.md  MIT License
     */
    class IMDB extends IMDBHelper {
        var $proxy,$proxyauth,$htmlData;
        var $query='',$genres='',$userid='',$page=1,$start=1,$itemsperpage=50;
        var $nopicturemovie = 'https://m.media-amazon.com/images/G/01/imdb/images/nopicture/medium/film-3385785534._CB470041827_.png';

        protected $url_search = 'https://www.imdb.com/search/title?title_type=feature&adult=include&view=advanced';
        protected $url_movie = 'https://www.imdb.com/title/';
        protected $url_artist = 'https://www.imdb.com/search/name?name=';

        public function urlBuilder($type){
            switch($type){
                case 'artist':
                    return $this->url_artist.rawurlencode($this->query).'&start='.$this->start.'&count='.$this->itemsperpage;
                case 'movie':
                    return $this->url_movie.rawurlencode($this->query);
                default:
                    return $this->url_search.'&title='.rawurlencode($this->query).'&genres='.$this->genres.'&role='.$this->userid.'&page'.$this->page.'&count='.$this->itemsperpage;
            }
        }

        public function search(){
            $this->sanitizer();
            $this->sendRequest($this->urlBuilder('search'));
            return $this;
        }

        public function getResponse(){
            if (!empty($this->htmlData)){
                return $this->htmlData;
            }
            return '';
        }

        public function getList(){
            if (!empty($this->htmlData)){
                $crawler = new Crawler($this->htmlData);
                $crawler1 = $crawler->filter('.lister-item.mode-advanced')->each(function (Crawler $node, $i) {
                    $imgfilter = $node->filter('.lister-item-image.float-left > a > img');
                    $content = $node->filter('.lister-item-content');
                    $contentheader = $content->filter('h3.lister-item-header');
                    $contentbody = $content->filter('p');
                    $contentrating = $content->filter('.ratings-bar > .inline-block.ratings-imdb-rating');
                    $contentvotes = $contentbody->filter('.sort-num_votes-visible > span[name="nv"]');
                    if ($this->isContains('nopicture',($imgfilter->count()?$imgfilter->attr('loadlate'):''))){
                        $img = $this->nopicturemovie;
                    } else {
                        $img = str_replace($imgfilter->attr('height'),268,str_replace($imgfilter->attr('width'),182,($imgfilter->count()?$imgfilter->attr('loadlate'):'')));
                    }
                    return [
                        'id' => ($imgfilter->count()?$imgfilter->attr('data-tconst'):''),
                        'title' => ($imgfilter->count()?$imgfilter->attr('alt').' '.str_replace([' ','TV','Movie'],'',$contentheader->filter('span.lister-item-year.text-muted.unbold')->text()):''),
                        'thumbnail' => ($imgfilter->count()?$imgfilter->attr('loadlate'):''),
                        'image' => $img,
                        'description' => $this->cleanString((($contentbody->count()?$contentbody->eq(1)->text():''))),
                        'genre' => ($contentbody->filter('.text-muted > span.genre')->count()?trim($contentbody->filter('.text-muted > span.genre')->eq(0)->text()):''),
                        'runtime' => ($contentbody->filter('.text-muted > span.runtime')->count()?$contentbody->filter('.text-muted > span.runtime')->eq(0)->text():''),
                        'rating' => (float)($contentrating->count()?$contentrating->attr('data-value'):''),
                        'votes' => (float)($contentvotes->count()?$contentvotes->attr('data-value'):''),
                        'gross' => [
                            'number' => (int)str_replace(',','',($contentvotes->eq(1)->count()?$contentvotes->eq(1)->attr('data-value'):'')),
                            'money' => ($contentvotes->eq(1)->count()?$contentvotes->eq(1)->text():'$0')
                        ]
                    ];
                });
                $navigation = $crawler->filter('.lister.list.detail.sub-list');
                $nav = $navigation->filter('.desc');
                $items = explode('title',($nav->count()?$nav->text():''));
                $total_item = 0;
                if(!empty($items[0])){
                    $items2 = explode('of',$items[0]);
                    if(!empty($items2[1])){
                        $total_item = (int)trim(str_replace(',','',$items2[1]));
                    } else {
                        $total_item = (int)trim(str_replace(',','',$items[0]));
                    }
                }
                $totalpages = (int)ceil($total_item/$this->itemsperpage);
                $first = (int)($nav->filter('span.lister-current-first-item')->count()?str_replace(',','',$nav->filter('span.lister-current-first-item')->text()):'1');
                $last = (int)($nav->filter('span.lister-current-first-item')->count()?str_replace(',','',$nav->filter('span.lister-current-last-item')->text()):$total_item);
                $crawler2 = [
                    'records_total' => $total_item,
                    'records_count' => ($last-($first-1)),
                    'number_item_first' => $first,
                    'number_item_last' => $last,
                    'page_now' => (int)$this->page,
                    'page_total' => $totalpages
                ];    
                if(!empty($crawler1)){
                    return [
                        'result' => $crawler1,
                        'navigation' => $crawler2,
                        'status' => 'success',
                        'message' => 'Data found.'
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'Data not found.'
                    ];
                }
            }
            return [
                'status' => 'error',
                'message' => 'Failed to get response from server.'
            ];
        }

        public function getListJson($options=0,$depth=512){
            $json = new JSON;
            return $json->withLog()->encode($this->getList(),$options,$depth);
        }

        //Search Artist===============

        public function searchArtist(){
            $this->sanitizer();
            $this->sendRequest($this->urlBuilder('artist'));
            return $this;
        }

        public function getArtistList() {
            if(!empty($this->htmlData)){
                $crawler = new Crawler($this->htmlData);
                $crawler1 = $crawler->filter('.lister-item.mode-detail')->each(function (Crawler $node, $i) {
                    $img = $node->filter('.lister-item-image > a');
                    $imgchild = $img->filter('img');
                    $headline = $node->filter('.lister-item-content > p.text-muted.text-small');
                    $content = $node->filter('.lister-item-content > p:not(.text-muted)');
                    return [
                        'id' => ($img->count()?str_replace('/name/','',$img->attr('href')):''),
                        'name' => ($imgchild->count()?$imgchild->attr('alt'):''),
                        'image' => ($imgchild->count()?$imgchild->attr('src'):''),
                        'headline' => $this->cleanString(($headline->count()?$headline->text():'')),
                        'description' => $this->cleanString(($content->count()?$content->text():''))
                    ];
                });
                $navigation = $crawler->filter('.article');
                $nav = $navigation->filter('.desc');
                $items = explode('name',($nav->count()?$nav->text():''));
                $total_item = 0;
                $first = $this->start;
                if(!empty($items[0])){
                    $items2 = explode('of',$items[0]);
                    if(!empty($items2[1])){
                        $total_item = (int)trim(str_replace(',','',$items2[1]));
                        $tmp = explode('-',$items2[0]);
                        $first = (int)$this->cleanString($tmp[0]);
                    } else {
                        $total_item = (int)trim(str_replace(',','',$items[0]));
                    }
                }
                $totalpages = (int)ceil($total_item/$this->itemsperpage);
                $last = (($first+$crawler->filter('.lister-item.mode-detail')->count())-1);
                $crawler2 = [
                    'records_total' => $total_item,
                    'records_count' => ($last-($first-1)),
                    'page_total' => $totalpages,
                    'number_item_first' => $first,
                    'number_item_last' => $last,
                    'next_start' => ((($last+1)<$total_item)?($last+1):0)
                ];    
                if(!empty($crawler1)){
                    return [
                        'result' => $crawler1,
                        'navigation' => $crawler2,
                        'status' => 'success',
                        'message' => 'Data found.'
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'Data not found.'
                    ];
                }
            }
            return [
                'status' => 'error',
                'message' => 'Failed to get response from server.'
            ];
        }

        public function getArtistListJson($options=0,$depth=512){
            $json = new JSON;
            return $json->withLog()->encode($this->getArtistList(),$options,$depth);
        }

        //Find Movie================

        public function find(){
            $this->sanitizer();
            $this->sendRequest($this->urlBuilder('movie'));
            return $this;
        }

        public function getMovieTitle(){
            if (!empty($this->htmlData)){
                $crawler = new Crawler($this->htmlData);
                return trim(str_replace(' - IMDb','',$crawler->filterXpath("//meta[@name='title']")->attr('content')));
            }
            return "";
        }

        public function getMovieDescription(){
            if (!empty($this->htmlData)){
                $crawler = new Crawler($this->htmlData);
                return trim($crawler->filterXpath("//meta[@name='description']")->attr('content'));
            }
            return "";
        }

        public function getMovieRuntime(){
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Runtime:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    preg_match( '~>(.*)<~Uis' , $tmp2[0], $time );
                    return (int)str_replace(' min','',$time[1]);
                } else {
                    return "";
                }
            }
            return "";
        }

        public function getMovieRuntimeSecond(){
            $minutes = str_replace(' min','',$this->getMovieRuntime());
            if (!empty($minutes)){
                return ( $minutes * 60 );
            }
            return "";
        }

        public function getMovieRuntimeFormatted(){
            $seconds = $this->getMovieRuntimeSecond();
            if (!empty($seconds)){
                return gmdate('H:i:s',$seconds);
            }
            return "";
        }

        public function getMovieRuntimeHuman(){
            if (!empty($this->htmlData)){
                $crawler = new Crawler($this->htmlData);
                $crawler = $crawler->filterXpath("//time");
                return ($crawler->count()?trim($crawler->text()):'');
            }
            return "";
        }

        public function getMovieTagline(){
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Taglines:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    $data = trim(str_replace("\n","",$tmp2[0])); 
                    $data = preg_replace('#<em.*?>([^>]*)</em>#i', '', $data);
                    $data = preg_replace('#<span.*?>([^>]*)</span>#i', '', $data);
                    $data = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $data);
                    return trim($data);
                } else {
                    return "";
                }
            }
            return "";
        }

        public function getMovieStoryline(){
            if (!empty($this->htmlData)){
                $tmp = explode('<div class="inline canwrap" itemprop="description">',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    /**/
                    $data = str_replace(["\n","<p>","</p>"],"",$tmp2[0]);
                    $data = preg_replace('#<span.*?>([^>]*)</span>#i', '', $data);
                    $data = preg_replace('#<em.*?>([^>]*)</em>#i', '', $data);
                    $data = preg_replace('#<a.*?>([^>]*)</a>#i', '$1', $data);
                    return trim($data);
                } else {
                    return $this->getMeta('description');
                }
            }
            return "";
        }

        public function getMoviePlot() {
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Plot Keywords:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains('<span>|</span>',$tmp2[0])){
                        $tmp3 = explode('<span>|</span>',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode('<span class="itemprop">', $key);
                            if (!empty($data[1])){
                                $data2 = explode("</span>",$data[1]);
                                $data3 .= trim($data2[0]).', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode('<span class="itemprop">', $tmp2[0]);
                        if (!empty($data[1])){
                            $data2 = explode("</span>",$data[1]);
                            return trim($data2[0]);
                        }
                    }
                }
            }
            return "";
        }

        public function getMovieAKA(){
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Also Known As:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains('<span',$tmp2[0])){
                        $tmp3 = explode('<span',$tmp2[0]);
                        return trim($tmp3[0]);
                    } else {
                        return trim($tmp2[0]);
                    }
                }
            }
            return "";
        }

        public function getMovieRelease(){
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Release Date:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains('<span',$tmp2[0])){
                        $tmp3 = explode('<span',$tmp2[0]);
                        return trim($tmp3[0]);
                    } else {
                        return trim($tmp2[0]);
                    }
                }
            }
            return "";
        }

        public function getMovieReleaseDate(){
            $release = $this->getMovieRelease($this->htmlData);
            if (!empty($release)){
                $data = explode(' (',$release);
                if (!empty($data[0])){
                    if (strlen($data[0]) == '4'){
                        return $data[0];
                    } else {
                        return date('Y-m-d',strtotime($data[0]));
                    }
                }
            }
            return "";
        }

        public function getMovieReleaseYear(){
            $release = $this->getMovieRelease($this->htmlData);
            if (!empty($release)){
                $data = explode(' (',$release);
                if (!empty($data[0])){
                    if (strlen($data[0]) == '4'){
                        return $data[0];
                    } else {
                        return date('Y',strtotime($data[0]));
                    }
                }
            }
            return "";
        }

        public function getMovieCountry() {
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Country:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains('<span class="ghost">|</span>',$tmp2[0])){
                        $tmp3 = explode('<span class="ghost">|</span>',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode("\n".">", $key);
                            if (!empty($data[1])){
                                $data2 = explode("</a>",$data[1]);
                                $data3 .= $data2[0].', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode("\n".">", $tmp2[0]);
                        if (!empty($data[1])){
                            $data2 = explode("</a>",$data[1]);
                            return $data2[0];
                        }
                    }
                }
            }
            return "";
        }

        public function getMovieGenre() {
            if (!empty($this->htmlData)){
                $tmp = explode('<h4 class="inline">Genres:</h4>',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains('<span>|</span>',$tmp2[0])){
                        $tmp3 = explode('<span>|</span>',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode('inf"'."\n".'> ', $key);
                            if (!empty($data[1])){
                                $data2 = explode("</a>",$data[1]);
                                $data3 .= $data2[0].', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode('inf"'."\n".'> ', $tmp2[0]);
                        $data2 = explode("</a>",$data[1]);
                        return $data2[0];
                    }
                }
            }
            return "";
        }

        public function getMovieDirector() {
            if (!empty($this->htmlData)){
                if($this->isContains('<h4 class="inline">Directors:</h4>',$this->htmlData)){
                    $tmp = explode('<h4 class="inline">Directors:</h4>',$this->htmlData);
                } else {
                    $tmp = explode('<h4 class="inline">Director:</h4>',$this->htmlData);
                }
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains(',',$tmp2[0])){
                        $tmp3 = explode(',',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode("\n".'>', $key);
                            if (!empty($data[1])){
                                $data2 = explode("</a>",$data[1]);
                                $data3 .= trim($this->convertAccents($data2[0])).', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode("\n".'>', $tmp2[0]);
                        if (!empty($data[1])){
                            $data2 = explode("</a>",$data[1]);
                            return trim($this->convertAccents($data2[0]));
                        }
                    }
                }
            }
            return "";
        }

        public function getMovieWriter() {
            if (!empty($this->htmlData)){
                if($this->isContains('<h4 class="inline">Writers:</h4>',$this->htmlData)){
                    $tmp = explode('<h4 class="inline">Writers:</h4>',$this->htmlData);
                } else {
                    $tmp = explode('<h4 class="inline">Writer:</h4>',$this->htmlData);
                }
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains(',',$tmp2[0])){
                        $tmp3 = explode(',',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode("\n".'>', $key);
                            if (!empty($data[1])){
                                $data2 = explode("</a>",$data[1]);
                                $data3 .= trim($this->convertAccents($data2[0])).', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode("\n".'>', $tmp2[0]);
                        if (!empty($data[1])){
                            $data2 = explode("</a>",$data[1]);
                            return trim($this->convertAccents($data2[0]));
                        }
                    }
                }
            }
            return "";
        }

        public function getMovieStar() {
            if (!empty($this->htmlData)){
                if($this->isContains('<h4 class="inline">Stars:</h4>',$this->htmlData)){
                    $tmp = explode('<h4 class="inline">Stars:</h4>',$this->htmlData);
                } else {
                    $tmp = explode('<h4 class="inline">Star:</h4>',$this->htmlData);
                }
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    if($this->isContains(',',$tmp2[0])){
                        $tmp3 = explode(',',$tmp2[0]);
                        $data3 = null;
                        foreach ($tmp3 as $key){
                            $data = explode("\n".'>', $key);
                            if(!empty($data[1])){
                                $data2 = explode("</a>",$data[1]);
                                $data3 .= trim($this->convertAccents($data2[0])).', ';
                            }
                        }
                        $result = substr($data3, 0, -2);
                        return $result;
                    } else {
                        $data = explode("\n".'>', $tmp2[0]);
                        if (!empty($data[1])){
                            $data2 = explode("</a>",$data[1]);
                            return trim($this->convertAccents($data2[0]));
                        }
                    }
                }
            }
            return "";
        }

        public function getMovieCast() {
            if (!empty($this->htmlData)){
                $tmp = explode('<tr><td colspan="4" class="castlist_label">',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</table>',$tmp[1]);
                    $data1 = str_replace(['"odd"','"even"'],'"findResult"',$tmp2[0]);
                    $tmp3 = explode('<tr class="findResult">',$data1);    
                    $data3 = '';
                    if (!empty($tmp3)){
                        foreach($tmp3 as $key){
                            $data2 = explode('</tr>',$key);    
                            preg_match_all( '|<img.*?title=[\'"](.*?)[\'"].*?>|i' , $data2[0], $img );
                            if (!empty($img[ 1 ][ 0 ])){
                                $data3 .= trim($this->convertAccents($img[ 1 ][ 0 ])).', ';
                            }
                        }
                        $data3 = substr($data3, 0, -2);
                    } else {
                        $data3 = '';
                    }
                    return $data3;
                }
            }
            return "";
        }

        public function getMovieTrailer() {
            if(!empty($this->htmlData)) {
                $YoutTubeSearchQuery = urlencode($this->getProperty('og:title')." trailer");
                $YoutTubeSearchQuery = preg_replace('/[^A-Za-z0-9]\+/', '', $YoutTubeSearchQuery);
                $YouTubeURL = "https://www.youtube.com/results?search_query=" . $YoutTubeSearchQuery;
                $YouTubeHTML = $this->sendRequest($YouTubeURL,true);
                preg_match('~href="/watch\?v=(.*)"~Uis',$YouTubeHTML,$match);
                if (!empty($match[1])) return $match[1];
            }
            return "";
        }
    
        public function getMovieTrailerFull() {
            $id = $this->getMovieTrailer();
            if (!empty($id)){
                return 'https://www.youtube.com/watch?v='.$this->getMovieTrailer();
            }
            return "";
        }

        public function getMovieTrailerEmbed() {
            $id = $this->getMovieTrailer();
            if (!empty($id)){
                return '<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$this->getMovieTrailer().'?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
            }
            return "";
        }

        public function getMovieImage(){
            if (!empty($this->htmlData)){
                $tmp = explode('<div class="poster">',$this->htmlData);
                if (!empty($tmp[1])){
                    $tmp2 = explode('</div>',$tmp[1]);
                    preg_match( '~src="(.*)"~Uis' , $tmp2[0], $img );
                    return $img[1];
                } else {
                    return $this->nopicturemovie;
                }
            } else {
                return $this->nopicturemovie;
            }
        }

        public function getMovieData(){
            if (!empty($this->htmlData)){
                return [
                    'id' => $this->query,
                    'url' => $this->url_movie.$this->query,
                    'title' => $this->getMovieTitle(),
                    'aka' => $this->getMovieAKA(),
                    'poster' => $this->getProperty('og:image'),
                    'poster_thumbnails' => $this->getMovieImage(),
                    'tagline' => $this->getMovieTagline(),
                    'description' => $this->getMeta('description'),
                    'storyline' => $this->getMovieStoryline(),
                    'director' => $this->getMovieDirector(),
                    'writers' => $this->getMovieWriter(),
                    'stars' => $this->getMovieStar(),
                    'casts' => $this->getMovieCast(),
                    'genres' => $this->getMovieGenre(),
                    'plot' => $this->getMoviePlot(),
                    'country' => $this->getMovieCountry(),
                    'release' => $this->getMovieRelease(),
                    'release_date' => $this->getMovieReleaseDate(),
                    'release_year' => (int)$this->getMovieReleaseYear(),
                    'trailer_id' => $this->getMovieTrailer(),
                    'trailer_link' => $this->getMovieTrailerFull(),
                    'trailer_embed' => $this->getMovieTrailerEmbed(),
                    'rating' => (float)$this->getSpanValue('itemprop="ratingValue"'),
                    'votes' => (int)str_replace(',','',$this->getSpanValue('class="small" itemprop="ratingCount"')),
                    'runtime_minutes' => $this->getMovieRuntime(),
                    'runtime_seconds' => $this->getMovieRuntimeSecond(),
                    'runtime_formatted' => $this->getMovieRuntimeFormatted()
                ];
            }
            return [];
        }

        public function getMovie(){
            if(!empty($this->htmlData)){
                if($this->isContains('<div id="error" class="error_code_404">',$this->htmlData)){
                    return [
                        'status' => 'error',
                        'message' => 'Data not found.'
                    ];
                } else {
                    return [
                        'result' => $this->getMovieData(),
                        'status' => 'success',
                        'message' => 'Data found.'
                    ];
                }
            }
            return [
                'status' => 'error',
                'message' => 'Failed to get response from server.'
            ];
        }

        public function getMovieJson($options=0,$depth=512){
            $json = new JSON;
            return $json->withLog()->encode($this->getMovie(),$options,$depth);
        }

    }