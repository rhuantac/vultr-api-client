<?php

/**
 * Vultr.com API Client
 * @package vultr
 * @version 0.0.1
 * @author  https://github.com/usefulz
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @see     https://github.com/usefulz/vultr-api-client
 */

class Vultr
{

  /**
   * API Token
   * @access private
   * @type string $api_token Vultr.com API token
   * @see https://my.vultr.com/settings/
   */

  private $api_token = '';

  /**
   * API Endpoint
   * @access public
   * @type string URL for Vultr.com API
   */

  public $endpoint = 'https://api.vultr.com/v1/';

  /**
   * Current Version
   * @access public
   * @type string Current version number
   */

  public $version = '0.0.1';

  /**
   * User Agent
   * @access public
   * @type string API User-Agent string
   */

  public $agent = 'Vultr.com API Client';

  /**
   * Debug Variable
   * @access public
   * @type bool Debug API requests
   */

  public $debug = TRUE;

  /**
   * Snapshots Variable
   * @access public
   * @type mixed Array to store snapshot IDs
   */

  public $snapshots = array();

  /**
   * Plans Variable
   * @access public
   * @type mixed Array to store VPS Plan IDs
   */

  public $plans     = array();

  /**
   * Regions Variable
   * @access public
   * @type mixed Array to store available regions
   */

  public $regions   = array();

  /**
   * Scripts Variable
   * @access public
   * @type mixed Array to store startup scripts
   */

  public $scripts   = array();

  /**
   * Servers Variable
   * @access public
   * @type mixed Array to store server data
   */

  public $servers   = array();

  /**
   * OS List Variable
   * @access public
   * @type mixed Array to store OS list
   */

  public $oses   = array();

  /**
   * SSH Keys variable
   * @access public
   * @type mixed Array to store SSH keys
   **/

  public $ssh_keys = array();

  /**
   * Response code variable
   * @access public
   * @type int Holds HTTP response code from API
   **/

  public $response_code = 0;

  /**
   * Response code variable
   * @access public
   * @type bool Determines whether to include the response code, default: false
   **/

  public $get_code = false;

  /**
   * Constructor function
   * @param string $token
   * @see https://my.vultr.com/settings/
   * @return void
   */

  public function __construct($token)
  {
    $this->api_token = $token;
    $this->snapshots = self::snapshot_list();
    $this->scripts   = self::startupscript_list();
    $this->regions   = self::regions_list();
    $this->servers   = self::server_list();
    $this->plans     = self::plans_list();
    $this->oses      = self::os_list();
    $this->ssh_keys  = self::sshkeys_list();
  }

  /**
   * Get Account info
   * @see https://www.vultr.com/api/#account_info
   * @return mixed
   */

  public function account_info()
  {
    return self::get('account/info');
  }

  /**
   * Get OS list
   * @see https://www.vultr.com/api/#os_os_list
   * @return mixed
   */

  public function os_list()
  {
    return self::get('os/list');
  }

  /**
   * List available snapshots
   * @see https://www.vultr.com/api/#snapshot_snapshot_list
   * @return mixed
   */

  public function snapshot_list()
  {
    return self::get('snapshot/list');
  }

  /**
   * Destroy snapshot
   * @see https://www.vultr.com/api/#snapshot_destroy
   * @param int $snapshot_id
   * @return int HTTP response code
   */

  public function snapshot_destroy($snapshot_id)
  {
    $args = array('SNAPSHOTID' => $snapshot_id);
    return self::code('snapshot/destroy', $args);
  }

  /**
   * Create snapshot
   * @see https://www.vultr.com/api/#snapshot_create
   * @param int $server_id
   */

  public function snapshot_create($server_id)
  {
    $args = array('SUBID' => $server_id);
    return self::post('snapshot/create', $args);
  }

  /**
   * List available ISO iamges
   * @see https://www.vultr.com/api/#iso_list
   * @return mixed Available ISO images
   **/

  public function iso_list()
  {
    return self::get('iso/list');
  }

  /**
   * List available plans
   * @see https://www.vultr.com/api/#plans_plan_list
   * @return mixed
   */

  public function plans_list()
  {
    return self::get('plans/list');
  }

  /**
   * List available regions
   * @see https://www.vultr.com/api/#regions_region_list
   * @return mixed
   */

  public function regions_list()
  {
    return self::get('regions/list');
  }

  /**
   * Determine region availability
   * @see https://www.vultr.com/api/#regions_region_available
   * @param int $datacenter_id
   * @return mixed VPS plans available at given region
   */

  public function regions_availability($datacenter_id)
  {
    $did = (int) $datacenter_id;
    return self::get('regions/availability?DCID=' . $did);
  }

  /**
   * List startup scripts
   * @see https://www.vultr.com/api/#startupscript_startupscript_list
   * @return mixed List of startup scripts
   */

  public function startupscript_list()
  {
    return self::get('startupscript/list');
  }

  /**
   * Update startup script
   * @param int $script_id
   * @param string $name
   * @param string $script script contents
   * @return int HTTP response code
   **/

   public function startupscript_update($script_id, $name, $script)
   {
     $args = array(
       'SCRIPTID' => $script_id,
       'name' => $name,
       'script' => $script
     );
     return self::code('startupscript/update', $args);
   }

  /**
   * Destroy startup script
   * @see https://www.vultr.com/api/#startupscript_destroy
   * @param int $script_id
   * @return int HTTP respnose code
   */

  public function startupscript_destroy($script_id)
  {
    $args = array('SCRIPTID' => $script_id);
    return self::code('startupscript/destroy', $args);
  }

  /**
   * Create startup script
   * @see https://www.vultr.com/api/#startupscript_create
   * @param string $script_name
   * @param string $script_contents
   * @return int Script ID
   */

  public function startupscript_create($script_name, $script_contents)
  {
    $args = array(
      'name' => $script_name,
      'script' => $script_contents
    );
    $script = self::post('startupscript/create', $args);
    return (int) $script['SCRIPTID'];
  }

  /**
   * Determine server availability
   * @param int $region_id Datacenter ID
   * @param int $plan_id VPS Plan ID
   * @return bool Server availability
   * @throws Exception if VPS Plan ID is not available in specified region
   */

  public function server_available($region_id, $plan_id)
  {
    $availability = self::regions_availability((int) $region_id);
    if (!in_array((int) $plan_id, $availability))
    {
      throw new Exception('Plan ID ' . $plan_id . ' is not available in region ' . $region_id);
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * List servers
   * @see https://www.vultr.com/api/#server_server_list
   * @return mixed List of servers
   */

  public function server_list()
  {
    return self::get('server/list');
  }

  /**
   * Display server bandwidth
   * @see https://www.vultr.com/api/#server_bandwidth
   * @param int $server_id
   * @return mixed Bandwidth history
   */

  public function bandwidth($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    return self::get('server/bandwidth', $args);
  }



  /**
   * List IPv4 Addresses allocated to specified server
   * @see https://www.vultr.com/api/#server_list_ipv4
   * @param int $server_id
   * @return mixed IPv4 address list
   */

  public function list_ipv4($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    $ipv4 = self::get('server/list_ipv4', $args);
    return $ipv4[(int) $server_id];
  }

  /**
   * Create IPv4 address
   * @see https://www.vultr.com/api/#server_create_ipv4
   * @param int $server_id
   * @param string Reboot server after adding IP: <yes|no>, default: yes
   * @return int HTTP response code
   **/

  public function ipv4_create($server_id, $reboot = 'yes')
  {
    $args = array(
      'SUBID' => $server_id,
      'reboot' => ($reboot == 'yes' ? 'yes' : 'no')
    );
    return self::code('server/create_ipv4', $args);
  }

  /**
   * Destroy IPv4 Address
   * @see https://www.vultr.com/api/#server_destroy_ipv4
   * @param int $server_ID
   * @param string $ip IPv4 address
   * @return int HTTP response code
   **/

  public function destroy_ipv4($server_id, $ip4)
  {
    $args = array(
      'SUBID' => $server_id,
      'ip' => $ip4
    );
    return self::code('server/destroy_ipv4', $args);
  }

  /**
   * Set Reverse DNS for IPv4 address
   * @see https://www.vultr.com/api/#server_reverse_set_ipv4
   * @param string $ip
   * @param string $rdns
   * @return int HTTP response code
   */

  public function reverse_set_ipv4($ip, $rdns)
  {
    $args = array(
      'ip' => $ip,
      'entry' => $rdns
    );
    return self::code('server/reverse_set_ipv4', $args);
  }

  /**
   * Set Default Reverse DNS for IPv4 address
   * @see https://www.vultr.com/api/#server_reverse_default_ipv4
   * @param string $server_id
   * @param string $ip
   * @return int HTTP response code
   */

  public function reverse_default_ipv4($server_id, $ip)
  {
    $args = array(
      'SUBID' => (int) $server_id,
      'ip' => $ip
    );
    return self::code('server/reverse_default_ipv4', $args);
  }

  /**
   * List IPv6 addresses for specified server
   * @see https://www.vultr.com/api/#server_list_ipv6
   * @param int $server_id
   * @return mixed IPv6 allocation info
   */

  public function list_ipv6($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    $ipv6 = self::get('server/list_ipv6', $args);
    return $ipv6[(int) $server_id];
  }

  /**
   * Set Reverse DNS for IPv6 address
   * @see https://www.vultr.com/api/#server_reverse_set_ipv6
   * @param int $server_id
   * @param string $ip
   * @param string $rdns
   * @return int HTTP response code
   */

  public function reverse_set_ipv6($server_id, $ip, $rdns)
  {
    $args = array(
      'SUBID' => (int) $server_id,
      'ip' => $ip,
      'entry' => $rdns
    );
    return self::code('server/reverse_set_ipv6', $args);
  }

  /**
   * Delete IPv6 Reverse DNS
   * @see https://www.vultr.com/api/#server_reverse_delete_ipv6
   * @param int $server_id
   * @param string $ip6 IPv6 address
   * @return int HTTP response code
   **/

  public function reverse_delete_ipv6($server_id, $ip6)
  {
    $args = array(
      'SUBID' => $server_id,
      'ip' => $ip6
    );
    return self::code('server/reverse_delete_ipv6', $args);
  }

  /**
   * Reboot server
   * @see https://www.vultr.com/api/#server_reboot
   * @param int $server_id
   * @return int HTTP response code
   */

  public function reboot($server_id)
  {
    $args = array('SUBID' => $server_id);
    return self::code('server/reboot', $args);
  }

  /**
   * Halt server
   * @see https://www.vultr.com/api/#server_halt
   * @param int $server_id
   * @return int HTTP response code
   */

  public function halt($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    return self::code('server/halt', $args);
  }

  /**
   * Start server
   * @see https://www.vultr.com/api/#server_start
   * @param int $server_id
   * @return int HTTP response code
   */

  public function start($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    return self::code('server/start', $args);
  }

  /**
   * Destroy server
   * @see https://www.vultr.com/api/#server_destroy
   * @param int $server_id
   * @return int HTTP response code
   */

  public function destroy($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    return self::code('server/destroy', $args);
  }

  /**
   * Reinstall OS on an instance
   * @see https://www.vultr.com/api/#server_reinstall
   * @param int $server_id
   * @return int HTTP response code
   */

  public function reinstall($server_id)
  {
    $args = array('SUBID' => (int) $server_id);
    return self::code('server/reinstall', $args);
  }

  /**
   * Set server label
   * @see https://www.vultr.com/api/#server_label_set
   * @param int $server_id
   * @param string $label
   * @return int HTTP response code
   */

  public function label_set($server_id, $label)
  {
    $args = array(
      'SUBID' => (int) $server_id,
      'label' => $label
    );
    return self::code('server/label_set', $args);
  }

  /**
   * Restore Server Snapshot
   * @see https://www.vultr.com/api/#server_restore_snapshot
   * @param int $server_id
   * @param string $snapshot_id Hexadecimal string with Restore ID
   * @return int HTTP response code
   */

  public function restore_snapshot($server_id, $snapshot_id)
  {
    $args = array(
      'SUBID' => (int) $server_id,
      'SNAPSHOTID' => preg_replace('/[^a-f0-9]/', '', $snapshot_id)
    );
    return self::code('server/restore_snapshot', $args);
  }

  /**
   * Restore Backup
   * @param int $server_id
   * @param string $backup_id
   * @return int HTTP response code
   **/

  public function restore_backup($server_id, $backup_id)
  {
    $args = array(
      'SUBID' => $server_id,
      'BACKUPID' => $backup_id
    );
    return self::code('server/restore_backup', $args);
  }

  /**
   * Create Server
   * @see https://www.vultr.com/api/#server_create
   * @param int $region_id
   * @param int $plan_id
   * @param int $os_id
   * @return FALSE if plan is not available in specified region
   * @return int Server ID if creation is successful
   */

  public function create($config)
  {

    $region_id = (int) $config['DCID'];
    $plan_id   = (int) $config['VPSPLANID'];
    $os_id     = (int) $config['OSID'];

    try
    {
      $available = self::server_available($region_id, $plan_id);
    }
    catch (Exception $e)
    {
      return FALSE;
    }

    $create = self::post('server/create', $config);
    return (int) $create['SUBID'];

  }


  /**
   * SSH Keys List method
   * @see https://www.vultr.com/api/#sshkey_sshkey_list
   * @return FALSE if no SSH keys are available
   * @return mixed with whatever ssh keys get returned
   */

   public function sshkeys_list()
   {
     $try = self::get('sshkey/list');
     if (sizeof($try) < 1) return FALSE;
     return $try;
   }

  /**
   * SSH Keys Create method
   * @see https://www.vultr.com/api/#sshkey_sshkey_create
   * @param string $name
   * @param string $key [openssh formatted public key]
   * @return FALSE if no SSH keys are available
   * @return mixed with whatever ssh keys get returned
   */

  public function sshkey_create($name, $key)
  {
    $args = array(
      'name' => $name,
      'ssh_key'  => $key
    );
    return self::post('sshkey/create', $args);
  }

  /**
   * SSH Keys Update method
   * @see https://www.vultr.com/api/#sshkey_sshkey_update
   * @param string $key_id
   * @param string $name
   * @param string $key [openssh formatted public key]
   * @return int HTTP response code
   */

  public function sshkey_update($key_id, $name, $key)
  {
    $args = array(
      'SSHKEYID' => $key_id,
      'name'     => $name,
      'ssh_key'  => $key
    );
    return self::code('sshkey/update', $args);
  }

  /**
   * SSH Keys Destroy method
   * @see https://www.vultr.com/api/#sshkey_sshkey_destroy
   * @param string $key_id
   * @return int HTTP response code
   */

  public function sshkey_destroy($key_id)
  {
    $args = array('SSHKEYID' => $key_id);
    return self::code('sshkey/update', $args);
  }

  /**
   * GET Method
   * @param string $method
   * @param mixed $args
   */

  public function get($method, $args = FALSE)
  {
    $this->request_type = 'GET';
    $this->get_code = false; //Otherwise it will return code after using self::code(); 
    return self::query($method, $args);
  }

   /**
    * CODE Method
    * @param string $method
    * @param mixed $args
    * @return mixed if no exceptions thrown
    **/

  public function code($method, $args = FALSE)
  {
    $this->request_type = 'POST';
    $this->get_code = true;
    return self::query($method, $args);
  }

  /**
   * POST Method
   * @param string $method
   * @param mixed $args
   */

  public function post($method, $args)
  {
    $this->request_type = 'POST';
    return self::query($method, $args);
  }

  /**
   * API Query Function
   * @param string $method
   * @param mixed $args
   */

  private function query($method, $args)
  {

    $url = $this->endpoint . $method . '?api_key=' . $this->api_token;

    if ($this->debug) echo $this->request_type . ' ' . $url . PHP_EOL;

    $_defaults = array(
      CURLOPT_USERAGENT => sprintf('%s v%s (%s)', $this->agent, $this->version, 'https://github.com/usefulz/vultr-api-client'),
      CURLOPT_HEADER => 0,
      CURLOPT_VERBOSE => 0,
      CURLOPT_SSL_VERIFYPEER => 1,
      CURLOPT_SSL_VERIFYHOST => 1,
      CURLOPT_HTTP_VERSION => '1.0',
      CURLOPT_FOLLOWLOCATION => 0,
      CURLOPT_FRESH_CONNECT => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_FORBID_REUSE => 1,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTPHEADER => array('Accept: application/json')
    );

    switch($this->request_type)
    {

      case 'POST':
        $post_data = http_build_query($args);
        $_defaults[CURLOPT_URL] = $url;
        $_defaults[CURLOPT_POST] = 1;
        $_defaults[CURLOPT_POSTFIELDS] = $post_data;
      break;

      case 'GET':
        if ($args !== FALSE)
        {
          $get_data = http_build_query($args);
          $_defaults[CURLOPT_URL] = $url . '&' . $get_data;
        } else {
          $_defaults[CURLOPT_URL] = $url;
        }
      break;

      default:break;
    }

    $apisess = curl_init();
    curl_setopt_array($apisess, $_defaults);
    $response = curl_exec($apisess);

    /**
     * Check to see if there were any API exceptions thrown
     * If so, then error out, otherwise, keep going.
     */

    try
    {
      self::isAPIError($apisess, $response);
    }
    catch(Exception $e)
    {
      curl_close($apisess);
      return $e->getMessage() . PHP_EOL;
    }


    /**
     * Close our session
     * Return the decoded JSON response
     */

    curl_close($apisess);
    $obj = json_decode($response, true);

    if ($this->get_code)
    {
      return (int) $this->response_code;
    }

    return $obj;
  }

  /**
   * API Error Handling
   * @param cURL_Handle $response_obj
   * @param string $response
   * @throws Exception if invalid API location is provided
   * @throws Exception if API token is missing from request
   * @throws Exception if API method does not exist
   * @throws Exception if Internal Server Error occurs
   * @throws Exception if the request fails otherwise
   */

  public function isAPIError($response_obj, $response)
  {

    $code = curl_getinfo($response_obj, CURLINFO_HTTP_CODE);

    if ($this->get_code)
    {
      $this->response_code = $code;
      break;
    }

    if ($this->debug) echo $code . PHP_EOL;

    switch($code)
    {
      case 200: break;
      case 400: throw new Exception('Invalid API location. Check the URL that you are using'); break;
      case 403: throw new Exception('Invalid or missing API key. Check that your API key is present and matches your assigned key'); break;
      case 405: throw new Exception('Invalid HTTP method. Check that the method (POST|GET) matches what the documentation indicates'); break;
      case 500: throw new Exception('Internal server error. Try again at a later time'); break;
      case 412: throw new Exception('Request failed: ' . $response); break;
      default:  break;
    }

  }

}
?>
