<?php
namespace SynonymAntonym;

/**
 * Get synonym and antonym from indonesian language
 * 
 * Source of synonym and antonym from http://www.sinonimkata.com
 * 
 * Developed By: Satmaxt Developer
 * Date: 22 July 2018
 */

use Sunra\PhpSimple\HtmlDomParser;

class Dictionary
{
    protected $ch = null;
    protected $results = null;
    protected $location = null;
    protected $word = 'satria';
    protected $url = 'http://www.sinonimkata.com';
    protected $userAgent = 'Opera/9.80 (J2ME/MIDP; Opera Mini/4.2 19.42.55/19.892; U; en) Presto/2.5.25';

    /**
     * For getting the referer link location
     * 
     * @return array
     */
    public function getLocation()
    {
        // start init and set url with builded url
        $this->ch = curl_init(
            $this->buildUrl($this->url, array(
                'q' => $this->word,
            ))
        );

        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, FALSE);

        $response = curl_exec($this->ch);
        
        // get Location index
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);

        curl_close($this->ch);

        // result of location
        // if empty location, the value will return false with index location
        $result = array(
            'location' => !empty($matches[1]) ? trim($matches[1][0]) : false,
        );

        return $result;
    }

    /**
     * Merge main url with query http url
     * 
     * @param string $url
     * @param array $data
     * @param boolean $search
     * @return string
     */
    public function buildUrl($url, $data = [], $search = true)
    {
        $search = $search ? '/search.php/?' : '/?';
        return $url.$search.http_build_query($data);
    }

    /**
     * Set the word that will search the antonym or synonym
     * 
     * @param string $word
     * @return object
     */
    public function word(string $word = '')
    {
        $this->word = $word == '' ? $this->word : $word;
        return $this;
    }

    /**
     * Get synonym or antonym data
     * 
     * @param string $type
     * @return array
     */
    public function getData($type)
    {
        // get referrer url from getLocation method
        $referrer = $this->getLocation();

        // if referrer url is empty, will return this
        if(!$referrer['location']) {
            return array(
                'status' => array(
                    'code' => 400,
                    'description' => 'Data untuk kata '.$this->word.' tidak ditemukan'
                )
            );
        }

        $urlData = $this->url.'/'.$referrer['location'];

        $this->ch = curl_init($urlData);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($this->ch);
        curl_close($this->ch);

        // if failed curl
        if(!$result) {
            return array(
                'status' => array(
                    'code' => 400,
                    'description' => 'Gagal mengambil data'
                )
            );
        }

        // parse data from curl with parseData method
        $this->results = $this->parseData($result);

        // if status is there of results property, the system will return the results
        // that the results have array information from parseData method
        if(isset($this->results['status'])) return $this->results;

        // if the type parameter is not all and the type of results not found.
        // the system will return error
        if($type !== "all" && !isset($this->results[$type])) return array(
            'status' => array(
                'code' => 400,
                'description' => 'Tidak ada data '.$type.' untuk kata '.$this->word,
            )
        );

        // if the type is all, the system will return all data that has been grabbed from url
        $willReturn = $type == "all" ? $this->results : $this->results[$type];

        return array(
            'status' => array(
                'code' => 200,
                'description' => 'OK'
            ),
            'title' => ucfirst($type),
            'data' => $willReturn,
        );
    }

    /**
     * Finding the antonym or synonym data using Sunra\PhpSimple\HtmlDomParser class
     * 
     * @param string $data
     * @return array
     */
    public function parseData($data)
    {
        // start parsing grabbed string with HtmlDomParser class
        $html = HtmlDomParser::str_get_html($data);

        // finding element like using querySelector or JQuery selector
        $tables = $html->find('td.link');
        $data = array();
        $results = array();

        foreach($tables as $table) {

            // get parent element of 'td.link'
            $tbl = $table->parent();
            
            // get the data synonym or antonym
            foreach($tbl->find('td',2)->find('a') as $row) {
                // place the data to array $data variable
                $data[] = $row->plaintext;
            }
            
            // place the array $data to array $results variable
            $results[$table->plaintext] = $data;

            // reset the $data variable to empty array
            $data = array();
        }

        // if grabbed data is not empty
        if(count($results) > 0) {
            return $results;
        }

        // if empty, the system will return this error
        return array(
            'status' => array(
                'code' => 400,
                'description' => 'Data untuk kata '.$this->word.' tidak ditemukan'
            )
        );
    }

    /**
     * Get antonym data
     * 
     * @return array
     */
    public function antonym()
    {
       return $this->getData('antonim');
    }

    /**
     * Get synonym data
     * 
     * @return array
     */
    public function synonym()
    {
       return $this->getData('sinonim');
    }

    /**
     * Get antonym and synonym data
     * 
     * @return array
     */
    public function all()
    {
       return $this->getData('all');
    }
}