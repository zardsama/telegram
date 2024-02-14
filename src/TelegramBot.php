<?PHP

namespace zardsama\telegram;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

Class TelegramBot {

    const API_URL = 'https://api.telegram.org';

    private string $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * get Telegram ChatId
     * @return array
     */
    public function getChatId() : array
    {
        $ret = $this->api('getUpdates');
        $ret = json_decode($ret);

        return [
            'fromId' => $ret->result[0]->message->from->id,
            'chatId' => $ret->result[0]->message->chat->id
        ];
    }

    /**
     * set Webhook url
     * @param string $url
     * @return string
     */
    public function setWebhook(string $url) : string
    {
        return $this->api('setWebhook', 'url='.urlencode($url));
    }

    /**
     * get Webhook url
     * @return string
     */
    public function getWebhook() : string
    {
        return $this->api('getWebhookInfo');
    }

    /**
     * push message
     * @param string $to
     * @param string $text
     * @param array $option
     * @return string
     */
    public function messagePush(string $to, string $text, array $option = []) : string
    {
        $add_param = '';
        if (is_array($option)) {
            $add_param .= '&'.http_build_query($option);

        }
        return $this->api('sendMessage', 'chat_id='.$to.'&text='.rawurlencode($text).$add_param);
    }

    /**
     * send Telegram API
     * @param string $api
     * @param string $param
     * @return string
     */
    public function api(string $api, string $param = '') : string
    {
        $url = self::API_URL.'/bot'.$this->token.'/'.$api.'?'.$param;

        try {
            $client = new CurlHttpClient();
            $response = $client->request(
                'GET',
                $url,
                [
                    'verify_peer' => false,
                    'verify_host' => false,
                    'timeout' => 10,
                    'headers' => [
                        'Content-Type:application/json'
                    ]
                ]
            );
            return $response->getContent();
        } catch (
            ClientExceptionInterface|
            ServerExceptionInterface|
            RedirectionExceptionInterface|
            TransportExceptionInterface $e
        ) {
            return $e->getMessage();
        }
    }

}