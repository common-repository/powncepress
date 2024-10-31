<?php
/**
 * Pownce PHP API Client
 * http://jeffhodsdon.com/ (for updates for now until google code is up)
 * http://powncephpclient.googlecode.com/ (not up yet)
 * View the example php file for some exampes of uses!
 * View the phpDoc for detailed function/method/obj explanations!
 * @author Jeff Hodsdon <onetrap@gmail.com>
 * @version  .01
 * @package Pownce
 **/

define("POWNCE_API_BASE_URL" , "http://api.pownce.com");
define("POWNCE_API_VERSION" , "1.1");
define("POWNCE_API_URL" , POWNCE_API_BASE_URL . "/" . POWNCE_API_VERSION . "/");
define("DEFAULT_NUM_OF_NOTES" , 20); //By default how many note objs you want to return when you call something

abstract class Pownce {

    var $user;
    var $debug;

    protected function __construct($user, $debug) {
        $this->user = $user;
        $this->debug = $debug;
    }

    /**
     * Get XML data
     * Gathers XML data from a url
     *
     * @param string $url
     * @return string String of XML to be parsed
     */
    protected function xml_data($url) {
        $url = parse_url($url);
        $data = " ";
        switch ($url['scheme']) {
            case "http":
                $fp = fsockopen($url['host'], 80, $errno, $errstr, 9);
                break;
            case "https":
                $fp = fsockopen($url['host'], 443, $errno, $errstr, 9);
                break;
        }
        if (!$fp) {
            die("Failed to connect! :( Error: " . $errstr);
        }
        else {
            $request = "GET " . $url['path'] . "?" . $url['query'] . " HTTP/1.0\r\n";
            $request .= "Host: " . $url['host'] . "\r\n";
            $request .= "User-Agent: Pownce\r\n";
            $request .= "Connection: Close\r\n\r\n";
            fputs($fp, $request);
            while (!feof($fp)) {
            	$data .= fgets($fp, 128);
            }
            fclose($fp);
            $exploded = explode("\r\n\r\n", $data, 2);
            $data = $exploded[1];
            return  $data;
        }
    }
}

/**
 * Pownce API XML Client
 * @subpackage  PownceXML
 */
class PowceXML extends Pownce {

    /**
     * Constructor
     * This is where you can define a user to open the client up for, they will be the default
     * person for each particular function/method
     * @param string $user
     * @param bool $debug
     */
    public function __construct($user = null, $debug = false) {
        parent::__construct($user, $debug);
    }

    /**
     * Get Public links
     * Get an array of public links
     * @param int $limit Optional: The number of links you want to return
     * @return array An array of simplexml objects
     */
    function get_public_links($limit = null) {
        return $this->get_public_notes($limit, 'links');
    }

    /**
     * Get Public messages
     * Get an array of public messages
     * @param int $limit Optional: The number of messages you want
     * @return array An array of simple xml objects
     */
    function get_public_messages($limit = null) {
        return $this->get_public_notes($limit, 'messages');
    }

    /**
     * Get public events
     * Get an array of public events
     * @param int $limit Optional: the number of events you want
     * @return array An array of simple xml objects
     */
    function get_public_events($limit = null) {
        return $this->get_public_notes($limit, 'events');
    }

    /**
     * Get username
     * Get the persons user name that the client is opened for
     * @param string $person Optional:  A particular person you want to get the username for
     * @return string The persons username
     */
    function get_username($person = null) {
        return $this->user;
    }

    /**
     * Get permalink
     * Get a persons permalink
     * @param string $person Optional: A particular person you want to get the username for
     * @return string
     */
    function get_permalink($person = null) {
        return $this->get_profile_field('permalink', $person);
    }

    /**
     * Get frist name
     * Get a persons first name
     * @param string $person Optional
     * @return string
     */
    function get_first_name($person = null) {
        return $this->get_profile_field('first_name', $person);
    }

    /**
     * Get short name
     * Get a persons short name
     * @param string $person Optional
     * @return string
     */
    function get_short_name($person = null) {
        return $this->get_profile_field('short_name', $person);
    }

    /**
     * Is pro
     * Boolean for if a person has a pro account
     * @param string $person Optional
     * @return bool
     */
    function is_pro($person = null) {
        if ($this->get_profile_field('is_pro', $person) == 1) {
        	return true;
        }
        else {
            return false;
        }
    }

    /**
     * Get blurb
     * Get a person's blurb
     * @param string $person Optional
     * @return string
     */
    function get_blurb($person = null) {
        return $this->get_profile_field('blurb', $person);
    }

    /**
     * Get location
     * Get a person's location
     * @param string $person Optional
     * @return string
     */
    function get_location($person = null) {
        return $this->get_profile_field('location', $person);
    }

    /**
     * Get country
     * Get a persons country
     * @param string $person Optional
     * @return string
     */
    function get_country($person = null) {
        return $this->get_profile_field('country', $person);
    }

    /**
     * Get gender
     * Get a person's gender
     * @param string $person Optional
     * @return string
     */
    function get_gender($person = null) {
        return $this->get_profile_field('gender', $person);
    }

    /**
     * Get age
     * Get a person's age
     * @param string $person Optional
     * @return int
     */
    function get_age($person = null) {
        return $this->get_profile_field('age', $person);
    }

    /**
     * Get profile data
     * Get a persons whole profile
     * @param string $field The field of info, EX: first_name, blurb, age, etc.
     * @param string $person Optional:  Default is the person that the client is opened for
     * @return string || array Since there are simplexml obj's inside a persons profile it can return both
     */
    function get_profile_field($field, $person = null) {
        if (!isset($person)) $person = $this->user;
        $data = $this->get_profile($person)->$field;
        return $data;
    }

    /**
     * Get Photo
     * Get a persons photo url, depending on which size you want
     * @param string $size Optional: Deafult is medium.  Choose from "tiny", "small", "smedium", "medium", "large"
     * @param string $person Optional: Deafult is the person the client is opened for.
     * @return string Image url
     */
    function get_photo($size = 'medium', $person = null) {
        if (!isset($person)) $person = $this->user;
        $person_profile = $this->get_profile($person);
        if ($size == "tiny") return $person_profile->profile_photo_urls->tiny_photo_url;
        if ($size == "small") return $person_profile->profile_photo_urls->small_photo_url;
        if ($size == "smedium") return $person_profile->profile_photo_urls->smedium_photo_url;
        if ($size == "medium") return $person_profile->profile_photo_urls->medium_photo_url;
        if ($size == "large") return $person_profile->profile_photo_urls->large_photo_url;
    }

    /**
     * Person's note count
     * @param string $person Optional:  Who's notes you want to count
     * @return int
     */
    function note_count($person = null) {
        return count($this->get_persons_public_notes_from($person, null, 9999999999));
    }

    /**
     * Person's message count
     * @param string $person Optional: Who's messages you want to count
     * @return int
     */
    function message_count($person = null) {
        return count($this->get_persons_public_messages('from', $person, 9999999999));
    }

    /**
     * Person's link count
     * @param string $person Optional: Who's links you want to count
     * @return int
     */
    function link_count($person = null) {
        return count($this->get_persons_public_links('from', $person, 9999999999));
    }

    /**
     * Person's event count
     * @param string $person Optional: Who's events you want to count
     * @return int
     */
    function event_count($person = null) {
        return count($this->get_persons_public_events('from', $person, 9999999999));
    }

    /**
     * Person's friend count
     * @return int
     */
    function friend_count($person = null) {
        return count($this->friend_obj_array($person));
    }

    /**
     * Person's fan count
     * @return int
     */
    function fan_count($person = null) {
        return count($this->fan_obj_array($person));
    }

    /**
     * Person's fan_of count
     * @return int
     */
    function fan_of_count($person = null) {
        return count($this->fan_of_obj_array($person));
    }

    /**
     *  Person's friends obj array.
     * Get an array of a person's friends
     * @param string $person Optional:  Which person to get the friends of
     * @param string $field Optional: Which field you are to get an array of, ex: "username", "first_name"
     * @return array
     **/
    function friend_obj_array($person = null, $field = null) {
        return $this->info_obj_array("friends", $person, $field);
    }

    /**
     * Person's fans obj array
     * Get an array of person's fans
     * @param string $person Optional:  Which person to get the fans of
     * @return array
     */
    function fan_obj_array($person = null, $field = null) {
        return $this->info_obj_array("fans", $person, $field);
    }

    /**
     * Person's fan_of obj array
     * Get an array of the people that are fans of a person
     * @param string $person Optional:  Which person who you want to find out which people they are fans of
     * @return  array
     */
    function fan_of_obj_array($person = null, $field = null) {
        return $this->info_obj_array("fan_of", $person, $field);
    }


    /**
     * Is friend
     * Boolean test to see if 2 people are friends
     * @param string $person The person to test
     * @param string $person2 Optional: If you dont want to test the person the client is opened for
     * @return bool
     */
    function is_friend($person, $person2 = null) {
        return $this->is_relation($person, $person2, "friends");
    }

    /**
     * Is fan, True if fan, false if not
     * @param string $person The person to test
     * @param string $person2 Optional: If you dont want to test the person the client is opened for
     * @return bool
     */
    function is_fan($person, $person2 = null) {
        return $this->is_relation($person, $person2, "fans");
    }

    /**
     * Is fan_of, True if they are a fan of, False if not
     * @param string $person Person to test
     * @param string $person2 Optional: If you dont want to test the person the client is opened for
     * @return bool
     */
    function is_fan_of($person, $person2 = null) {
        return $this->is_relation($person, $person2, "fan_of");
    }

    /**
     * Get notes from
     * Get an array of notes sent by a person, you may specify how many to get
     * @param string $person Optional:  The person to get the notes from
     * @param string $type Optional: The type of note, messages, links, or events
     * @param int $limit Optional: How many notes you want, if there is that many
     * @return array An array of simplexml obj for the notes
     */
    function get_persons_public_notes_from($person = null, $type = null, $limit = null) {
        return $this->get_persons_public_notes('from', $person, $type, $limit);
    }

    /**
     * Get notes to
     * Get an array of notes sent to a person, you may specify how many to get, and which kind
     * @param string $person Optional:  The person to get the notes from
     * @param string $type Optional: The type of note, messages, links, or events
     * @param int $limit Optional: How many notes you want, if there is that many
     * @return array An array of simplexml obj for the notes
     */
    function get_persons_public_notes_to($person = null, $type = null, $limit = null) {
        return $this->get_persons_public_notes('to', $person, $type, $limit);
    }

    /**
     * Get notes for
     * Get an array of all notes sent to and by a person, you may specify how many to get, and which kind
     * @param string $person Optional:  The person to get the notes from
     * @param string $type Optional: The type of note, messages, links, or events
     * @param int $limit Optional: How many notes you want, if there is that many
     * @return array An array of simplexml obj for the notes
     */
    function get_persons_public_notes_for($person = null, $type = null, $limit = null) {
        return $this->get_persons_public_notes('for', $person, $type, $limit);
    }

    /**
     * Get persons public messages
     * Get an array of all person's public messages, you may specify how many to get
     * @param string $rel The relation the messages have to the person have, either "from", "to", "for"(both sent and to)
     * @param string $person Optional: The person to get the messages from
     * @param int $limit Optional
     * @param array An array of messages
     */
    function get_persons_public_messages($rel, $person = null, $limit = DEFAULT_NUM_OF_NOTES) {
        return $this->get_persons_public_notes($rel, $person, 'messages', $limit);
    }

    /**
     * Get persons public links
     * Get an array of all person's public links, you may specify how many to get
     * @param string $rel The relation the links have to the person have, either "sent", "to", "all"(both sent and to)
     * @param string $person Optional: The person to get the links from
     * @param int $limit Optional
     * @param array An array of links
     */
    function get_persons_public_links($rel, $person = null, $limit = DEFAULT_NUM_OF_NOTES) {
        return $this->get_persons_public_notes($rel, $person, 'links', $limit);
    }

    /**
     * Get persons public events
     * Get an array of all person's public events, you may specify how many to get
     * @param string $rel The relation the events have to the person have, either "sent", "to", "all"(both sent and to)
     * @param string $person Optional: The person to get the events from
     * @param int $limit Optional
     * @param array An array of events
     */
    function get_persons_public_events($rel, $person = null, $limit = DEFAULT_NUM_OF_NOTES) {
        return $this->get_persons_public_notes($rel, $person, 'events', $limit);
    }

    /***Utility functions****/

    /**
     * Is relation, tests a relation to the user.  Returns true if they are.
     * @param string $person The person that you want to check the relation for
     * @param string $person2 Optional: if you want to test someone else, rather than who the client is opened for
     * @param string $relation What relation to check.  Either friends, fans, fan_of
     * @param string $name_type Optional: Either "username", "first_name", "short_name"
     * Default is "username"
     * @return  bool
     */
    function is_relation($person, $person2 = null, $relation, $name_type = "username") {
        if(!isset( $person2)) $person2 = $this->user;
        if ($relation == "friend") $relation = "friends";
        if ($relation == "fan") $relation = "fans";
        $person_array = $this->info_obj_array($relation,  $person2, $name_type);
        return in_array($person, $person_array);
    }

    /**
     * Get a person's profile.
     * Get person's profile data
     * @param string $person Optional: Default is whoever the client is opened for
     * @return array Profile obj data
     */
    function get_profile($person = null) {
        if (!isset($person)) $person = $this->user;
        $xml_url = POWNCE_API_URL . "users/" . $person . ".xml";
        $xml_file = parent::xml_data($xml_url);
        $xml_obj = simplexml_load_string($xml_file);
        return $xml_obj;
    }

    /**
     * Array of relation information, returns an array of particular infomation of people of certain relationship to that user.
     * @param string $relation Either "friends", "fans", or "fan_of"
     * @param string $person Optional: If you want to get data from an other person than the client is opened for
     * @param string $field Optional: Ex: username, blurb, etc
     * @return array
     */
    function info_obj_array($relation, $person = null, $field = "notset") {
        if (!isset($person)) $person = $this->user;
        for($i = 0; ; $i++) {
            //Currently the api has a max of 100 listings per xml doc
            $xml_url = POWNCE_API_URL . "users/" . $person . "/" . $relation . ".xml?limit=100&page=" . $i;
            $xml_file = parent::xml_data($xml_url);
            $xml_obj = simplexml_load_string($xml_file) or die("Could not load XML from API, sorry :(");
            if (count($xml_obj->user) > 0) {
                foreach ($xml_obj->user as $user_obj) {
                    static $curr_person = 0;
                    $curr_person++;
                    if ($field != "notset") {
                    	$person_array[$curr_person] = $user_obj->$field;
                    }
                    else {
                        $person_array[$curr_person] = $user_obj;
                    }
                }
            }
            else {
                break;
            }
        }
        return $person_array;
    }

    /**
     * Get Person's public notes
     * Gather a persons public notes, you may specify the relation of the note, whoms notes, the type, and how many you want.
     * @param string $rel The relation, either "for", "from", or "to"
     * @param string $person Optional: The person you want to get notes from/to or whatevs
     * @param string $type Optional: The type of note "messages", "links", or "events"
     * @param string $limit Optional: Default is 20  How many notes you want
     * @return array An array of simple xml obj's
     */
    function get_persons_public_notes($rel, $person = null, $type = null, $limit = DEFAULT_NUM_OF_NOTES) {
        if (!isset($person)) $person = $this->user;
        if ($limit <= 100) {
            $page_limit = $limit;
        }
        else {
            $page_limit = 100;
        }
        for($i = 0; ; $i++) {
            //Currently the api has a max of 100 listings per xml doc
            $xml_url = POWNCE_API_URL . "public_note_lists/" . $rel . "/" . $person . ".xml?limit=" . $page_limit . "&type=" . $type ."&page=" . $i;
            $xml_file = parent::xml_data($xml_url);
            $xml_obj = simplexml_load_string($xml_file) or die("Could not load XML from API, sorry :(");
            if (count($xml_obj->note) > 0 && count($note_array) < $limit) {
                foreach ($xml_obj->note as $note_obj) {
                    if (count($note_array) < $limit) {
                        static $curr_note = 0;
                        $curr_note++;
                        $note_array[$curr_note] = $note_obj;
                    }
                    else {
                        break;
                    }
                }
            }
            else {
                break;
            }
        }
        return $note_array;
    }

    /**
     * Get public notes
     * Gets most recent public notes, you may specify only a certain type aswell as a the number or limit you want.
     * @param string $limit The number of how many to show, if there are that many. 1 and up
     * @param string $type The trype of notes, "messages", "links", or "events"
     * @return array An array of simple xml obj's
     */
    function get_public_notes($limit = DEFAULT_NUM_OF_NOTES, $type = null) {
        if ($limit <= 100) {
            $page_limit = $limit;
        }
        else {
            $page_limit = 100;
        }
        for($i = 0; ; $i++) {
            //Currently the api has a max of 100 listings per xml doc
            $xml_url = POWNCE_API_URL . "public_note_lists.xml?limit=" . $page_limit . "&type=" . $type ."&page=" . $i;
            $xml_file = parent::xml_data($xml_url);
            $xml_obj = simplexml_load_string($xml_file) or die("Could not load XML from API, sorry :(");
            if (count($xml_obj->note) > 0 && count($note_array) < $limit) {
                foreach ($xml_obj->note as $note_obj) {
                    if (count($note_array) < $limit) {
                        static $curr_note = 0;
                        $curr_note++;
                        $note_array[$curr_note] = $note_obj;
                    }
                    else {
                        break;
                    }
                }
            }
            else {
                break;
            }
        }
        return $note_array;
    }
}

/**
 * Pownce API JSON Client
 * @subpackage  PownceJSON
 *
 */
class PownceJSON extends Pownce {

}

?>