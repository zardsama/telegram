<?PHP

	namespace zardsama\telegram;

	use zardsama\http\CurlConnection;

	Class TelegramBot {
		const API_URL = 'https://api.telegram.org';

		private $token;
		private $curl;

		public function __construct($token) {
			$this->token = $token;
		}

		public function getChatId() {
			$ret = $this->api('getUpdates');
			$ret = json_decode($ret);
			return array(
				'fromId' => $ret->result[0]->message->from->id,
				'chatId' => $ret->result[0]->message->chat->id
			);
		}

		public function setWebhook($url) {
			$ret = $this->api('setWebhook', 'url='.urlencode($url));
			return $ret;
		}

		public function getWebhook() {
			$ret = $this->api('getWebhookInfo');
			return $ret;
		}

		public function messagePush($to, $text) {
			return $this->api('sendMessage', 'chat_id='.$to.'&text='.rawurlencode($text));
		}

		public function api($api, $param = null) {
			$url = TelegramBot::API_URL.'/bot'.$this->token.'/'.$api.'?'.$param;
			$this->curl = new CurlConnection($url, 'GET');
			$this->curl->setHeader(array(
				'Content-Type:application/json',
			));
			$this->curl->exec();

			return $this->curl->getResult();
		}
	}

?>