<?php

namespace SocialAuther\Adapter;

class Vk extends AbstractAdapter
{
    public function __construct($config)
    {
        parent::__construct($config);

        $this->socialFieldsMap = array(
            'socialId'   => 'uid',
            'email'      => 'email',
            'avatar'     => 'photo_big',
            'birthday'   => 'bdate',
            'name'       => 'first_name',
            'lastName'   => 'last_name',
            'screenName' => 'screen_name'
        );

        $this->provider = 'vk';
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getSocialPage()
    {
        $result = null;

        if (isset($this->userInfo['screen_name'])) {
            $result = 'http://vk.com/' . $this->userInfo['screen_name'];
        }

        return $result;
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        $result = null;

        if (isset($this->userInfo['sex'])) {
            $result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
        }

        return $result;
    }

    /**
     * Get user connections (skype, facebook, twitter, livejournal, instagram)
     *
     * @return array|null
     */
    public function getConnections()
    {
        $result = array(
            'skype'         => null,
            'facebook'      => null,
            'twitter'       => null,
            'livejournal'   => null,
            'instagram'     => null
        );

        $result = array_replace($result, array_intersect_key($this->userInfo, $result));

        return $result;
    }

    /**
     * Authenticate and return bool result of authentication
     *
     * @return bool
     */
    public function authenticate()
    {
        $result = false;

        if (isset($_GET['code'])) {
            $params = array(
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri
            );

            $tokenInfo = $this->get('https://oauth.vk.com/access_token', $params);
            if (isset($tokenInfo['access_token'])) {
                $params = array(
                    'uids'         => $tokenInfo['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big,connections',
                    'access_token' => $tokenInfo['access_token']
                );

                $userInfo = $this->get('https://api.vk.com/method/users.get', $params);
                if (isset($userInfo['response'][0]['uid'])) {
                    $this->userInfo = $userInfo['response'][0];

                    if (isset($tokenInfo['email']))
                        $this->userInfo['email'] = $tokenInfo['email'];

                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    public function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'http://oauth.vk.com/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'scope'         => 'email',
                'redirect_uri'  => $this->redirectUri,
                'response_type' => 'code'
            )
        );
    }
}