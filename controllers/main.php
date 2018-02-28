<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * sample code for Tactile group
 * This controller supports login, signup, form validation functionalities
 * @author: akshay 
 * 
 *  

*/
class Main extends CI_Controller {

	public function index()
	{
		$this->login();
	}
        
        public function login(){
            $this->load->view('login');
        }
        
        public function signup(){
            
            $this->load->view('signup');
        }
        
        public function members(){
            if ($this->session->userdata('is_logged_in')){
             $this->load->view('members');   
            } else {
                redirect('main/restricted');
            } 
        }
        
        public function restricted(){
            $this->load->view('restricted');
        }
        
        public function login_validation(){
           
            $this->load->library('form_validation');
        
            $this->form_validation->set_rules('email','Email','required|trim|xss_clean|callback_validate_credentials');
        
            $this->form_validation->set_rules('password','Password','required|md5|trim');
       
            if($this->form_validation->run()){
                $data = array(
                    'email' => $this->input->post('email'),
                    'is_logged_in' => 1
                );
                $this->session->set_userdata($data);
                redirect('main/members');
            }else{
                $this->load->view('login');
            }   
        }
        
        public function signup_validation(){
            
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('email','Eamil','required|trim|valid_email|is_unique[users.email]');
            
            $this->form_validation->set_rules('password','Password','required|trim');
            
            $this->form_validation->set_rules('cpassword','Confirm Password','required|trim|matches[password]');
            
            $this->form_validation->set_message('is_unique',"That email already exists");
            
            if($this->form_validation->run()){
                //new id acceptance
                
                //generate a random key
                $key = md5(uniqid()); //generates random key for each registered user when validated
                
                $this->load->library('email',array('mailtype'=>'html'));
                $this->load->model('model_users');
                
                $this->email->from('cisample.com',"akshay");
                
               $this->email->to($this->input->post('email'));
               
               $this->email->subject('confirm your account');
               
               $message = "<p>Thank you for singing up!</p>";
               $message .= "<p><a href='".base_url()."main/register_user/$key'>click here</a> to confirm your account</p>";
               
               $this->email->message($message);
               
               //send an email to the user
               if ($this->model_users->add_temp_user($key)){
                  // if ($this->email->send()){
                   /* for now this wouldn't work as it is running on localhost, 
                    so the if condition is commented */
                        echo "email has been sent";
                       // }else {echo "could not send the email";}
               } else {echo "problem adding database";}
               
                //add them to the temp_users table
               
               
            } else {
                $this->load->view('signup');
            }
        }
        
        public function validate_credentials(){
            
            $this->load->model('model_users');
            
            if($this->model_users->can_log_in()){
                return true;
            }else{
                $this->form_validation->set_message('validate_credentials','Incorrect username/password.');
                return false;
            }
            
        }
        public function logout(){
            
            $this->session->sess_destroy();
            redirect('main/login');
        }
        
        public function register_user($key){
            $this->load->model('model_users');
            
            if($this->model_users->is_key_valid($key)){
                if ($newemail = $this->model_users->add_user($key)){
                    
                    $data = array(
                            'email' => $newemail,
                            'is_logged_in' => 1
                            );
                    
                    $this->session->set_userdata($data);
                    redirect('main/members');
                } else {echo "failed to add user , plaese try again";}
            }else{
                echo "invalid key";
            }
            
        }
}