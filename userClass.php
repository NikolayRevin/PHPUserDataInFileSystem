<?

namespace YourNameSpace;

/**
 * User data in file system
 * @author nikolay.revin
 */
class User {
    private $uid = null;
    private $arUserData = null;
    private $filePath = "/sitedata/base/users"; // path to users jsons folder

    public function __construct($email = "") {
        if (!$email) {
            throw new \Exception("No data");
        }

        $email = trim($email);
        $this->uid = md5(strrev($email));
    }

    private function GetUserFile() {
        return $_SERVER["DOCUMENT_ROOT"].$this->filePath."/".$this->uid.'.json';
    }

    public function GetUid() {
        return $this->uid;
    }

    /**
     * check isset user
     * @return bool
     */
    public function issetUser() {
        $fileUser = $this->GetUserFile();
        return file_exists($fileUser);
    }

	/**
     * get data 
     * @return array
     */
    public function GetData() {
        if ($this->arUserData == null) {
            $fileUser = $this->GetUserFile();
            if (file_exists($fileUser)) {
                $data = file_get_contents($fileUser);
                $this->arUserData = json_decode($data, 1);
            }
        }

        return $this->arUserData;
    }

    /**
     * set data 
     */
    public function SetData(array $arData) {
        $arCurrentData = (array)$this->GetData();
        $arData['id'] = $this->uid;
        $arData = array_merge($arCurrentData, $arData);

        $userFile = $this->GetUserFile();

        return file_put_contents($this->GetUserFile(), json_encode($arData));
    }

    /**
     * confirm
     */
    public function ConfirmEmail($token) {
		$arData = $this->GetData();

        if ($arData['token'] == $token) {
            $arData['token'] = '';
            $arData['confirm'] = 1;
            $this->SetData($arData);
            $this->sendConfirmEmail();
            return true
        }

        return false
    }

	/**
     * send for confirm email with link
     */
    public function sendCheckEmail() {
        $arData = $this->GetData();
        $to  = $arData["email"] ;
        $host = $_SERVER["SERVER_NAME"];
        $link = 'http://'.$host.'/?confirm=Y&email='.$to.'&token='.$arData["token"];
        $subject = "Подтвердите свой почтовый адрес";

        $message = '
                <html>
                    <body>
                        <p>
                            Здравствуйте, '.$arData["name"].'!
                             <br><br>
                            Пожалуйста, подтвердите свой почтовый адрес, для этого перейдите по ссылке <a href="'.$link.'" target="_blank">'.$link.'</a>.<br>
                        </p>
                    </body>
                </html>';

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: noreply@example.com\r\n";

        mail($to, "=?utf-8?B?".base64_encode($subject)."?=", $message, $headers);
    }

    /**
     * send if success confirm email
     */
    public function sendConfirmEmail() {
        $arData = $this->GetData();
        $to  = $arData["email"] ;

        $subject = "Вы подтвердили свой почтовый адрес";

        $message = '
                <html>
                    <body>
                        <p>
                            Здравствуйте, '.$arData["name"].'!
                             <br><br>
                            Вы подтвердили свой почтовый адрес.
                        </p>
                    </body>
                </html>';

        $headers  = "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "From: noreply@example.com\r\n";

        mail($to, "=?utf-8?B?".base64_encode($subject)."?=", $message, $headers);
    }
}
?>
