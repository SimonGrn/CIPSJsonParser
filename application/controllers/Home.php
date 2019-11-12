<?php
class Home extends MY_Base {

    public function index()
    {
        $this->load->model('Execution');
        $this->load->helper('my_duration');

        //get all data from GCP
        $GCP_files_list = [];
        $gcp_url = $this->config->item('GCP_URL');
        try {
            $t = file_get_contents($gcp_url);
            $xml = new SimpleXMLElement($t);
            foreach($xml->Contents as $content) {
                if (strpos((string)$content->Key, '.zip') !== false) {
                    $GCP_files_list[] = (string)$content->Key;
                }
            }

        } catch(Exception $e) {
            log_message('warning', "couldn't fetch files from GCP");
        }
        //get all data from executions
        $execution_list = $this->Execution->getAllInformation();
        //get all versions
        $versions_list = $this->Execution->getVersions();

        $full_list = [];
        foreach($execution_list->result() as $execution) {
            $full_list[date('Y-m-d', strtotime($execution->start_date))][] = $execution;
        }
        foreach($GCP_files_list as $item) {
            preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})-.*\.zip/', $item, $matches_filename);
            $full_list[$matches_filename[1]][] = $item;
        }
        uksort($full_list, "compare_date_keys");

        $content_data = [
            'execution_list' => $full_list,
            'versions_list' => $versions_list,
            'gcp_files_list' => $GCP_files_list,
            'gcp_url' => $gcp_url
        ];

        $header_data = [
            'title' => "Nightlies reports",
            'js' => ['https://code.jquery.com/jquery-3.4.1.min.js']
        ];

        $this->display('home', $content_data, $header_data);
    }
}
