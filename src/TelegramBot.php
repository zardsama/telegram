<?PHP

/**
 * Telegram API
 * @noinspection PhpUnused
 */

namespace zardsama\telegram;

use RuntimeException;
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
     * @param ?string $parse_mode
     * @return string
     */
    public function messagePush(string $to, string $text, array $option = [], ?string $parse_mode = '') : string
    {
        $param = [
            'chat_id' => $to,
            'text' => $text,
        ];
        if ($parse_mode) {
            $param['parse_mode'] = $parse_mode;
        }
        if (is_array($option) && count($option)) {
            foreach ($option as $key => $value) {
                $param[$key] = $value;
            }
        }
        return $this->api('sendMessage', $param);
    }

    /**
     * push media group
     * @param string $to
     * @param array $media
     * @return string
     */
    public function mediaGroupPush(string $to, array $media) : string
    {
        $_media = [];
        foreach ($media as $url) {
            $_media[] = [
                'type' => 'photo',
                'media' => $url
            ];
        }

        $param = [
            'chat_id' => $to,
            'media' => array_slice($_media, 0, 10)
        ];

        return $this->api('sendMediaGroup', $param);
    }

    /**
     * send Telegram API
     * @param string $api
     * @param string|array $param
     * @return string
     * @throws RuntimeException
     */
    public function api(string $api, string|array $param = '') : string
    {
        $url = self::API_URL.'/bot'.$this->token.'/'.$api;
        if (is_array($param)) {
            $method = 'POST';
        } else {
            $method = 'GET';
            $url .= '?' . $param;
        }

        try {
            $client = new CurlHttpClient();
            $response = $client->request(
                $method,
                $url,
                [
                    'verify_peer' => false,
                    'verify_host' => false,
                    'timeout' => 10,
                    'headers' => [
                        'Content-Type: application/json'
                    ],
                    'json' => $param
                ]
            );

            $content = $response->getContent(false);
            if ($response->getStatusCode() == '200') {
                return $content;
            } else {
                $content = json_decode($content);
                throw new RuntimeException($content->description);
            }
        } catch (
            ClientExceptionInterface |
            ServerExceptionInterface |
            RedirectionExceptionInterface |
            TransportExceptionInterface $e
        ) {
            throw new RuntimeException($e->getMessage());
        }
    }

}