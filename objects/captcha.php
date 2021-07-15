<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
if(!empty($_GET['PHPSESSID'])){
    session_write_close();
    session_id($_GET['PHPSESSID']);
    _error_log("captcha: session_id changed to ". $_GET['PHPSESSID']);
    session_start();
}
class Captcha{
    private $largura, $altura, $tamanho_fonte, $quantidade_letras;

    function __construct($largura, $altura, $tamanho_fonte, $quantidade_letras) {
        $this->largura = $largura;
        $this->altura = $altura;
        $this->tamanho_fonte = $tamanho_fonte;
        $this->quantidade_letras = $quantidade_letras;
    }


    public function getCaptchaImage() {
        global $global;
        header('Content-type: image/jpeg');
        $imagem = imagecreate($this->largura,$this->altura); // define a largura e a altura da imagem
        $fonte = $global['systemRootPath'] . 'objects/monof55.ttf'; //voce deve ter essa ou outra fonte de sua preferencia em sua pasta
        $preto  = imagecolorallocate($imagem, 0, 0, 0); // define a cor preta
        $branco = imagecolorallocate($imagem, 255, 255, 255); // define a cor branca

        // define a palavra conforme a quantidade de letras definidas no parametro $quantidade_letras
        //$letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789';
        $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789';
        $palavra = substr(str_shuffle($letters), 0, ($this->quantidade_letras));
        if(User::isAdmin()){
            $palavra = "admin";
        }
        _session_start();
        $_SESSION["palavra"] = $palavra; // atribui para a sessao a palavra gerada
        //_error_log("getCaptchaImage: ".$palavra." - session_name ". session_name()." session_id: ". session_id());
        for ($i = 1; $i <= $this->quantidade_letras; $i++) {
            imagettftext(
                $imagem,
                $this->tamanho_fonte,
                rand(-10, 10),
                ($this->tamanho_fonte*$i),
                ($this->tamanho_fonte + 10),
                $branco,
                $fonte,
                substr($palavra, ($i - 1), 1)
            ); // atribui as letras a imagem
        }
        imagejpeg($imagem); // gera a imagem
        imagedestroy($imagem); // limpa a imagem da memoria
        //_error_log("getCaptchaImage _SESSION[palavra] = ($_SESSION[palavra]) - session_name ". session_name()." session_id: ". session_id());
    }

    static public function validation($word) {
        if(User::isAdmin()){
            return true;
        }
        _session_start();
        if(empty($_SESSION["palavra"])){
            _error_log("Captcha validation Error: you type ({$word}) and session is empty - session_name ". session_name()." session_id: ". session_id());
            return false;
        }
        $validation = (strcasecmp($word, $_SESSION["palavra"]) == 0);
        if(!$validation){
            _error_log("Captcha validation Error: you type ({$word}) and session is ({$_SESSION["palavra"]})- session_name ". session_name()." session_id: ". session_id());
        }
        return $validation;
    }

}
