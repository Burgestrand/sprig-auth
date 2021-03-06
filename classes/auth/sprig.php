<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Sprig Auth driver.
 *
 * @package    Sprig Auth
 * @author     Paul Banks
 */
class Auth_Sprig extends Auth {

	/**
	 * Checks if a session is active.
	 *
	 * @param   string   role name
	 * @param   array    collection of role names
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		$status = FALSE;

		// Get the user from the session
		$user = $this->session->get($this->config['session_key']);
		
		if ( ! is_object($user))
		{
			// Attempt auto login
			if ($this->auto_login())
			{
				// Success, get the user back out of the session
				$user = $this->session->get($this->config['session_key']);
			}
		}

		if (is_object($user) AND $user instanceof Model_User AND $user->loaded())
		{
			// Everything is okay so far
			$status = TRUE;

			if ( ! empty($role))
			{

				// If role is an array
				if (is_array($role))
				{
					// Check each role
					foreach ($role as $role_iteration)
					{
						// If the user doesn't have the role
						if( ! $user->has_role($role_iteration))
						{
							// Set the status false and get outta here
							$status = FALSE;
							break;
						}
					}
				}
				else
				{
					// Check that the user has the given role
					$status = $user->has_role($role);
				}
			}
		}

		return $status;
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function _login($user, $password, $remember)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);
		
		// If the passwords match, perform a login
		if ($user->has_role('login') AND $user->password === $password)
		{
		    if ($remember === TRUE)
		    {
		        // Generate autologin token
		        $token = sha1(uniqid('sprig-auth', TRUE));
		        
		        // Save token to user data
		        $user->values(array('autologin' => $token))->update();
		        
		        // Save token cookie
		        cookie::set('authautologin', $token, $this->config['lifetime']);
		    }
		    
			// Finish the login
			$this->complete_login($user);

			return TRUE;
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username
	 * @return  boolean
	 */
	public function force_login($user)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);

		// Mark the session as forced, to prevent users from changing account information
		$this->session->set('auth_forced', TRUE);

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		if ($token = cookie::get('authautologin'))
		{
			// Load the user from the given token
			$user = Sprig::factory('user', array('autologin' => $token))->load();			
			
			if ($user->load() AND $user->loaded())
			{
				// Complete the login with the found data
				$this->complete_login($user);

				// Automatic login was successful
				return TRUE;
			}
			else
			{
			    // delete token
                cookie::delete('authautologin');
			}
		}

		return FALSE;
	}

	/**
	 * Log a user out and remove any auto-login cookies.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove user autologin token
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = TRUE)
	{
		if ($token = cookie::get('authautologin'))
		{
			// Delete the autologin cookie to prevent re-login
			cookie::delete('authautologin');
			
			// Clear the autologin token from the database
			$user = $this->get_user();
			if ($user && $logout_all)
			{
                $user->values(array('autologin' => NULL))->update();
            }
		}

		return parent::logout($destroy);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($user)
	{
		// Make sure we have a user object
		$user = $this->_get_object($user);

		return $user->password;
	}

	/**
	 * Complete the login for a user by setting session data: user_id, username, roles
	 *
	 * @param   object   user model object
	 * @return  void
	 */
	protected function complete_login($user)
	{
		return parent::complete_login($user);
	}
	
	/**
	 * Convert a unique identifier string to a user object
	 * 
	 * @param mixed $user
	 * @return Model_User
	 */
	protected function _get_object($user)
	{
		if ( ! is_object($user))
		{
			// Load the user using special factory method
			$user = Model_User::factory($user)->load();			
		}
		
		return $user;
	}

} // End Auth_Sprig_Driver