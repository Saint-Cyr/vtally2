<?php

namespace VtallyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


use Ddeboer\DataImport\ValueConverter\StringToObjectConverter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use VtallyBundle\Component\CSVTypes;
use Ddeboer\DataImport\Reader\CsvReader;

use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer\DoctrineWriter;


class DefaultController extends Controller
{
    public function downloadAction($transactionType, $id = null)
    {
        //Get the default handler service
        $defaultHandler = $this->get('vtally.default_handler');
        
        $outPut = $defaultHandler->processDownload($transactionType, $id);
        
        if(!$outPut['document']){
            throw $this->createNotFoundException('not found');
        }
        
        $type = $outPut['type'];
        
        $doc = $outPut['document'];
        
        $files = array();
        
        foreach ($doc as $key => $d) {
            //Make sure the pinkSheet exist in order to avoid generating error
            if($d){
                array_push($files, getcwd()."/pinkSheet/presidential/".$d->getName());
            }
        }
       
        $zip = new \ZipArchive();
        
        $zipName = 'Documents-'.time().".zip";
        
        $zip->open(getcwd()."/pinkSheet/".$type.'/'.$zipName,  \ZipArchive::CREATE);
        foreach ($files as $f) {
            $zip->addFromString(basename($f),  file_get_contents($f)); 
        }
        
        $zip->close();
        $response = new Response();
        
        if(file_exists(getcwd()."/pinkSheet/".$type.'/'.$zipName)){
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type(getcwd()."/pinkSheet/".$type.'/'.$zipName));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($zipName) . '"');
            $response->headers->set('Content-length', filesize(getcwd()."/pinkSheet/".$type.'/'.$zipName));
            $response->sendHeaders();
            $response->setContent(readfile(getcwd()."/pinkSheet/".$type.'/'.$zipName));
            header('Content-Type', 'application/zip');
            header('Content-disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($zipName));
            readfile($zipName);
        }
        
        return $response;
    }
    
    public function pinkSheetAction($transactionType, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        if($transactionType == 'presidential'){
            //Get the pollingStation in order to get the related pinkSheet
            $pollingStation = $em->getRepository('VtallyBundle:PollingStation')->find($id);
            //Make sure pollingStation exists
            if(!$pollingStation){
                throw $this->createNotFoundException('Polling Station of ID: '.$id.' not found from the DB.');
            }
            $pinkSheet = $pollingStation->getPrPinkSheet();
        }elseif($transactionType == 'parliamentary'){
            //Get the pollingStation in order to get the related pinkSheet
            $pollingStation = $em->getRepository('VtallyBundle:PollingStation')->find($id);
            //Make sure pollingStation exists
            if(!$pollingStation){
                throw $this->createNotFoundException('Polling Station of ID: '.$id.' not found from the DB.');
            }
            $pinkSheet = $pollingStation->getPaPinkSheet();
        }else{
            throw $this->createNotFoundException('transactionType: '.$transactionType.' not found.');
        }
        
        return $this->render('VtallyBundle:Default:pink_sheet.html.twig', array(
            'transactionType' => $transactionType,
            'pollingStation' => $pollingStation,
            'pinkSheet' => $pinkSheet,
        ));
    }
    
    public function nationalAction()
    {
        //Get the statisticHandler service
        $statisticHandler = $this->get('vtally.statistic_handler');
        //Get entity manager
        $em = $this->getDoctrine()->getManager();
        //Get the set of parties
        $parties = $em->getRepository('PaBundle:PaParty')->findAll();
        //Get the set of independent
        $indepentCandidates = $em->getRepository('PaBundle:IndependentCandidate')->findAll();
        //Get all the constituencies
        $constituencies = $em->getRepository('VtallyBundle:Constituency')->findAll();
        $paVoteCast = $statisticHandler->getSiteNumber($constituencies, $parties, $indepentCandidates);
        //Get presidential vote cast for national level
        $presidentialVoteCast = $statisticHandler->getPresidentialNation();
        return $this->render('VtallyBundle:vote:charts.html.twig', array(
            'presidentialVoteCast' => $presidentialVoteCast,
            'paVoteCast' => $paVoteCast,
        ));
    }

    public function dashboardAction()
    {
        //when logout, goes to the login page
        //Get the authorization checker
        $authChecker = $this->get('security.authorization_checker');
        if(!$authChecker->isGranted("ROLE_SUPER_ADMIN")){
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        //Get the entity manager
        $em = $this->getDoctrine()->getManager();
        //Get the statisticHandler service
        $statisticHandler = $this->get('vtally.statistic_handler');
        //Get presidential vote cast for national level
        $presidentialVoteCast = $statisticHandler->getPresidentialNation();
        //Get the set of parties
        $parties = $em->getRepository('PaBundle:PaParty')->findAll();
        //Get the set of independent
        $indepentCandidates = $em->getRepository('PaBundle:IndependentCandidate')->findAll();
        //Get all the constituencies
        $constituencies = $em->getRepository('VtallyBundle:Constituency')->findAll();
        $paVoteCast = $statisticHandler->getSiteNumber($constituencies, $parties, $indepentCandidates);
        return $this->render('VtallyBundle:vote:dashboard.html.twig', array(
            'presidentialVoteCast' => $presidentialVoteCast,
            'paVoteCast' => $paVoteCast,
        ));
    }
    
    public function constituencyAction(Request $request, $page)
    {
        //Get the entity manager
        $em = $this->getDoctrine()->getManager();
        
        //Get the keryWord in the case of search
        $keyWord = $request->get('keyWord');
        
        //Process according the case whether it's search or not
        if($keyWord){
            //Get the list of all the constituencies
            $constituencies = $em ->getRepository('VtallyBundle:Constituency')
                               ->getForSearch($keyWord);
        }else{
            //Get the list of all the constituencies
            $constituencies = $em ->getRepository('VtallyBundle:Constituency')
                               ->getConstituencies(10, $page);
        }
                               
        return $this->render('VtallyBundle:vote:constituencies.html.twig', array(
            'constituencies' => $constituencies,
            'page' => $page,
            'numberPerPage' => ceil(count($constituencies)/10),
        ));
    }
    
    public function prRegionListAction()
    {
        //Get all the regions
        $regions = $this->get('doctrine')->getManager()->getRepository('VtallyBundle:Region')->findAll();
        //Passe regions to the view
        return $this->render('VtallyBundle:Default:pr_region_list.html.twig', array('regions' => $regions));
    }
    
    public function prPinkSheetRegionListAction()
    {
        //Get all the regions
        $regions = $this->get('doctrine')->getManager()->getRepository('VtallyBundle:Region')->findAll();
        //Passe regions to the view
        return $this->render('VtallyBundle:Default:pr_pink_sheet_region_list.html.twig', array('regions' => $regions));
    }
    
    public function paRegionListAction()
    {
        //Get all the regions
        $regions = $this->get('doctrine')->getManager()->getRepository('VtallyBundle:Region')->findAll();
        //Passe regions to the view
        return $this->render('VtallyBundle:Default:pa_region_list.html.twig', array('regions' => $regions));
    }
    
    public function paPinkSheetRegionListAction()
    {
        //Get all the regions
        $regions = $this->get('doctrine')->getManager()->getRepository('VtallyBundle:Region')->findAll();
        //Passe regions to the view
        return $this->render('VtallyBundle:Default:pa_pink_sheet_region_list.html.twig', array('regions' => $regions));
    }
    
    public function settingGeneralAction()
    {
        return $this->render('VtallyBundle:Default:setting_general.html.twig');
    }
    
    public function pollingStationAction(Request $request, $page)
    {
        //Get the entity manager
        $em = $this->getDoctrine()->GetManager();
        //Get the keryWord in the case of search
        $keyWord = $request->get('keyWord');
        
        //Process according the case whether it's search or not
        if($keyWord){
            //Get the list of all the constituencies
            $pollingStations = $em->getRepository('VtallyBundle:PollingStation')
                                  ->getForSearch($keyWord);
        }else{
            //Get the list of all the pollingStations
            $pollingStations = $this->getDoctrine()->getManager()
                                    ->getRepository('VtallyBundle:PollingStation')
                                    ->getPollingStations(10, $page);
        }
        
        return $this->render('VtallyBundle:vote:polling_stations.html.twig', array(
            'pollingStations' => $pollingStations,
            'page' => $page,
            'numberPerPage' => ceil(count($pollingStations)/10),
        ));
    }
    
    public function importCSVAction(Request $request) 
    {
        // Get FileId to "import"
        $param = $request->request;
        $fileId = (int)trim($param->get("fileId"));

        $curType=trim($param->get("fileType"));
        $uploadedFile = $request->files->get("csvFile");
        
        //var_dump($uploadedFile->guessExtension());exit;
        
        // if upload was not ok, just redirect to "shortyStatWrongPArameters"
        if (!CSVTypes::existsType($curType) || $uploadedFile==null){
            $this->get('session')->getFlashBag()
                 ->add('sonata_flash_info', 'Sorry... cannot upload the file(s). Check documentation or see super-administrator');
            
            return $this->redirect($this->generateUrl('sonata_admin_dashboard'));
        }
        
        // generate dummy dir
        $dummyImport = getcwd()."/dummyImport";
        $fname= $uploadedFile->getClientOriginalName();
        $filename=$dummyImport."/".$fname;
        @mkdir($dummyImport);
        @unlink($filename);
        
        // move file to dummy filename
        $uploadedFile->move($dummyImport, $fname);            

        echo "Starting to Import file. Type: ".CSVTypes::getNameOfType($curType)."<br />n";
        
        /** By @Saint-Cyr **/
        $file = new \SplFileObject($dummyImport.'/'.$fname);
        $reader = new CsvReader($file);
        //set the hander in order to build an associative array
        $reader->setHeaderRowNumber(0);
        // Create the workflow from the reader
        $workflow = new Workflow($reader);
        //doctrine writer
        $em = $this->getDoctrine()->getManager();
        
        $doctrineWriter = new DoctrineWriter($em, CSVTypes::getEntityClass($curType));
        //disable truncate
        $doctrineWriter->disableTruncate();
        
        try{
            
            $workflow->addWriter($doctrineWriter);
            
            //treat the type separatly
            if(CSVTypes::getNameOfType($curType) == 'Polling Station'){
                $repository = $em->getRepository('VtallyBundle:Constituency');
                $property = 'constituency';
                $route = 'admin_vtally_pollingstation_list';
            }elseif(CSVTypes::getNameOfType($curType) == 'Constituency'){
                $repository = $em->getRepository('VtallyBundle:Region');
                $property = 'region';
                $route = 'admin_vtally_constituency_list';
            }
            
            $converter = new StringToObjectConverter($repository, 'code');
            $workflow->addValueConverter($property, $converter);
            $workflow->process();
            
        }catch (UniqueConstraintViolationException $e){

            $this->get('session')->getFlashBag()
                 ->add('sonata_flash_error', 'Error: cannot import the CSV file because it contains one or many entry that allready '
                         . 'exists in the Data Base');
            return $this->redirect($this->generateUrl('sonata_admin_dashboard'));
        }
    
        
        /** End if code by S@int-Cyr **/
        $this->get('session')->getFlashBag()
             ->add('sonata_flash_success', CSVTypes::getNameOfType($curType)." CSV file uploaded successfully !");
            
        return $this->redirect($this->generateUrl($route));
    }
    
    public function singleNotificationAction($type, $id)
    {
        //Get the entity manager
        $em = $this->getDoctrine()->getManager();
        //Case of default notification
        if($type == 'vtally'){
            $notification = $em->getRepository('VtallyBundle:Notification')->find($id);
        }elseif($type == 'presidential'){
            $notification = $em->getRepository('PrBundle:PrNotification')->find($id);
        }elseif($type == 'parliamentary'){
            $notification = $em->getRepository('PaBundle:PaNotification')->find($id);
        }
        
        return $this->render('VtallyBundle:Default:single_notification.html.twig',
                             array('notification' => $notification,
                                   'type' => $type,));
        
    }
    
    public function notificationAction()
    {
        //Get the notification handler
        $notificationHandler = $this->get('vtally.notification_handler');
        $em = $this->get('doctrine')->getManager();
        //Online users
        $onlineUserObjects = $em->getRepository('UserBundle:User')->findByActive(true);
        $onlineUsers = array('number' => count($onlineUserObjects), 'onlineUserObjects' => $onlineUserObjects);
        //Over-voting
        $overVotingObjects = $em->getRepository('VtallyBundle:Notification')->findByType(array('type' => 'over-voting'));
        $overVotings = array('number' => count($overVotingObjects), 'overVotingObjects' => $overVotingObjects);
        //Matching votes
        $matchingVoteObjects = $em->getRepository('VtallyBundle:Notification')->findByType(array('type' => 'matching-vote'));
        $matchingVotes = array('number' => count($matchingVoteObjects), 'matchingVoteObjects' => $matchingVoteObjects);
        //Complited collation centers NB: to be called on the initializer server.
        //$collationCentersObjects = $notificationHandler->getComplitedCollationCenter();
        //Parliamentary vote-cast mismatch
        $paMismatchingVoteObjects = $em->getRepository('PaBundle:PaNotification')->findByType(array('type' => 'mismatching-vote'));
        $paMismatchingVote = array('number' => count($paMismatchingVoteObjects), 'paMismatchingVoteObjects' => $paMismatchingVoteObjects);
        //Presidential vote cast mismatch
        $prMismatchVoteObjects = $em->getRepository('PrBundle:PrNotification')->findByType(array('type' => 'mismatching-vote'));
        $prMismatchVote = array('number' => count($prMismatchVoteObjects), 'prMismatchVoteObjects' => $prMismatchVoteObjects);
        //Parliamentary pink sheet mismatch
        $paPinkSheetObjects = $em->getRepository('PaBundle:PaNotification')->findByType(array('type' => 'pink-sheet mismatch'));
        $paPinkSheet = array('number' => count($paPinkSheetObjects), 'paPinkSheetObjects' => $paPinkSheetObjects);
        //Presidential pink sheet mismatch
        $prPinkSheetObjects = $em->getRepository('PrBundle:PrNotification')->findByType(array('type' => 'pink-sheet mismatch'));
        $prPinkSheet = array('number' => count($prPinkSheetObjects), 'prPinkSheetObjects' => $prPinkSheetObjects);
        //Gether all the default data here
        $default = array('overVotings' => $overVotings, 'matchingVotes' => $matchingVotes,
                         'onlineUsers' => $onlineUsers);
        //Gether all the presidential here
        $prData = array('prPinkSheet' => $prPinkSheet, 'prMismatchVote' => $prMismatchVote);
        //Gether all the parliamentary here
        $paData = array('paPinkSheet' => $paPinkSheet, 'paMismatchVote' => $paMismatchingVote);       
        //Gether all the notifications in an array
        $notifications = array('presidential' => $prData, 'parliamentary' => $paData, 'default' => $default);
        
        
        //$notifications = array('prPinkSheet' => $prPinkSheet, 'paPinkSheet' => $parPinkSheet, 'prPinkSheetNumber' => count($prPinkSheet));
        return $this->render('VtallyBundle:Default:notification.html.twig', array('notifications' => $notifications)); 
    }
}