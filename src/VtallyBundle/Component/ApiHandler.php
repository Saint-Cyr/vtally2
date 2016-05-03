<?php

/*
 * This file is part of Components of VTALLY2 project
 * By contributor S@int-Cyr MAPOUKA
 * (c) YAME Group <info@yamegroup.com>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */
namespace VtallyBundle\Component; 
use PrBundle\Entity\PrParty;
use PrBundle\Entity\PrVoteCast;
use VtallyBundle\Entity\PollingStation;
use VtallyBundle\Entity\Region;
use VtallyBundle\Entity\Constituency;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class ApiHandler
{
    private $em;
    private $factory;
    private $manager;
    private $security;
    private $container;


    public function __construct($em, $factory, $user_provider, $securityToken, $container) 
    {
        $this->em = $em;
        $this->factory = $factory;
        $this->user_provider = $user_provider;
        $this->security = $securityToken;
        $this->container = $container;
    }
    
    public function login($inputData)
    {
        //if user is authentic then get $user object otherwise, get false;
        $user = $this->isAuthentic($inputData['username'], $inputData['password']);
        
        if($user){
            //Refresh the token time in order to initialize the session time
            $user->refreshTokenTime();
            $this->em->flush();
            
            return array('first_name' => $user->getFirstName(), 'pol_id' => $user->getPollingStation()->getName());
        }
        
        return array('Bad credentials.');
    }
    
    
    
    public function sendPresidentialVoteCast(array $inputData)
    {
        //make sure vote cast is for Presidential  
        if(!array_key_exists('pr_votes', $inputData)||($inputData['transaction_type'] != 'presidential')){
            
            return array('Invalid data structure.');
        }
        
        //See the API doc for more information 
        if(($this->validatorFactory2($inputData))&&($this->validatorFactory3($inputData))
                &&($this->isPresidentialVoteCastValid($inputData['pr_votes']))){
            
            //Get the polling Station
            $pollingStation = $this->em->getRepository('VtallyBundle:PollingStation')->find($inputData['pol_id']);
            //Make sure $pollingStation exist in the DB and it doesn't yet recieve presidential vote
            if((!$pollingStation) || ($pollingStation->isPresidential())){
                return array('Error: cannot send presidential vote cast.');
            }
            //Save the vote cast one by one in the DB. with theire respective relationship
            $prVotes = $inputData['pr_votes'];
            
            foreach ($prVotes as $partyName => $value){
                //Get the current PrParty from the DB.
                $prParty = $this->em->getRepository('PrBundle:PrParty')->findOneBy(array('name' => $partyName));
                //Instentiate a PrVoteCast
                $prVoteCast = new PrVoteCast();
                //Hydrate it with the right data
                $prVoteCast->setFigureValue($value);
                $prVoteCast->setPrParty($prParty);
                $prVoteCast->setPollingStation($pollingStation);
                //In the case wordValue functionality is used
                //$wordValue = some API or third party service...($value)
                //$prVoteCast->setWordValue($wordValue);
                //Persist all 
                $this->em->persist($prVoteCast);
            }
            
            //Set the PollingStation property presidential to true
            $pollingStation->setPresidential(true);
            //$this->em->flush();
            
            return array('presidential vote cast sent!');
            
        }
        
        return array('Error: cannot send presidential vote cast');
    }
    
    public function isPresidentialVoteCastValid(array $votes)
    {
        return true;
    }
    
    public function process(array $inputData)
    {
        //Make sure every request have the key action
        if(array_key_exists('action', $inputData)){
            
            switch ($inputData['action']){
                case 1:
                    return $this->login($inputData);
                    break;
                case 2:
                    
                    if($this->validatorFactory1($inputData)){
                        
                        return $this->sendPresidentialVoteCast($inputData);
                    }
                    //validatorFactory1 faild.
                    return array('Error: the concerned polling station is not activated.');
                    break;
            }
        }
        
        return false;
    }
    
    public function isDataStructureValid($inputData)
    {
        if(array_key_exists('action', $inputData)&&($this->validatorFactory1($inputData))){
            
            switch ($inputData['action']){
                case 1:
                    if((array_key_exists('username', $inputData))&&(array_key_exists('password', $inputData))&&
                            ((isset($inputData['username'])&&(isset($inputData['password']))))&&($this->validatorFactory1($inputData))){
                        return true;
                    }else{
                        return false;
                    }
                    break;
                case 2:
                    return true;
                    break;
            }
        }
        
        return false;
    }
    
    public function isAuthentic($username, $password)
    {
        /* Validate the User */
        //$user_manager = $this->container->get('fos_user.user_manager');
        $user_manager = $this->manager;
        //$factory = $this->container->get('security.encoder_factory');
        $factory = $this->factory;
        
        //To avoid thrownig an exception by FOSUserBundle in the case where username is wrong
        $user = $this->em->getRepository('UserBundle:User')->findOneBy(array('username' => $username));
        
        if(!$user){
            return false;
        }
        //The username is right
        $user = $this->user_provider->loadUserByUsername($username);
        
        $encoder = $factory->getEncoder($user);
        $validated = $encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt());
        if (!$validated) {
            
            http_response_code(400);
            return false;
       
        } else {
            
            $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
            //now the user is logged in
            $this->security->setToken($token);
            //now dispatch the login event
            //$request = $this->container->get("router.request_context");
            //$event = new InteractiveLoginEvent($request, $token);
            //$this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        return $user;
        
        }
    }
    
    public function validatorFactory1($inputData)
    {
        //In the case $inputData does have verifier_token key
        if(array_key_exists('verifier_token', $inputData)){
            //Get the user object based on the userToken
            $user = $this->em->getRepository('UserBundle:User')
                    ->findOneBy(array('userToken' => $inputData['verifier_token']));
        }

        //In the case $inputData does have username key when action = 1 (login)
        elseif(array_key_exists('username', $inputData)){
            //Get the user object based on the username
            $user = $this->em->getRepository('UserBundle:User')
                    ->findOneBy(array('username' => $inputData['username']));
        }
        
        //Continous only if the user exist in DB
        if(!$user){
            return array('user not found in DB.');
        }
        
        //Get the pollingStation related to $user
        $pollingStation = $user->getPollingStation();
        
        if(!$pollingStation){
            return false;
        }
        
        //The logic of the validation goes here
        if($pollingStation->isActive()){
            return true;
        }
        
        return false;
    }
    
    public function validatorFactory2($inputData)
    {
        if(((array_key_exists('transaction_type', $inputData))&&(array_key_exists('pol_id', $inputData))
                &&(array_key_exists('verifier_token', $inputData)))
                &&((isset($inputData['transaction_type'])&&(isset($inputData['pol_id'])
                &&(isset($inputData['verifier_token'])))))){
            
            return true;
        }
        
        return false;
    }
    
    public function validatorFactory3(array $inputData)
    {
        if(array_key_exists('verifier_token', $inputData)){
            //Get the user from the DB in order to compare it token to the sent one
            $user = $this->em->getRepository('UserBundle:User')->findOneBy(array('userToken' => $inputData['verifier_token']));
            
            if(!$user){
                
                return false;
            }
            
            //Get the setting
            $setting = $this->em->getRepository('VtallyBundle:Setting')->findOneBy(array('type' => 'default'));
            
            if(!$setting){
                //to be improved with a throw exception 
                var_dump('Setting not found in DB.');exit;
            }
            //The token must be valid and it age too
            //NB the parameter $tokenTime must be provided by the parameter system
            //$tokenTime (in minute) is the value of the max lapsed time of inactivity
            $tokenTime = $setting->getTokenTime();
            //For test purpose set $tokenTime  to any value
            //$tokenTime = 20;
            if(($inputData['verifier_token'] == $user->getUserToken())&&($user->isUserTokenValid($tokenTime))){
                //Refresh the tokenTime
                $user->refreshTokenTime();
                $this->em->flush();
                return true;
            }
            
            return false;
        }
        
        return false;
    }
}