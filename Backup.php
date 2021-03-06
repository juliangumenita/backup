<?php
  class Backup{
    const DEFAULT_KEY = "DEFAULT_KEY";
    const METHOD = "AES-256-CBC";
    const IV = "16_BYTES_LONG_IV";

    /* You can define your own backup key. */

    public static function create($path = "./", $key = self::DEFAULT_KEY){
      $temp = [];
      $folders = self::folders($path);
      $strings = self::strings($folders);
      foreach ($strings as $string) {
        array_push($temp, [
          "folder" => $string[0],
          "file" => $string[1],
          "content" => file_get_contents($string[0] . $string[1])
        ]);
      }
      $json = json_encode($temp, true);
      $file = "backup_" . self::random(). ".backup";
      file_put_contents($file, self::encrypt($json, $key));

      return $file;
    }

    public static function restore($path, $key = self::DEFAULT_KEY){
      $file = file_get_contents($path);
      $json = json_decode(
        self::decrypt($file, $key), true
      );

      $backup = str_replace(".backup", null, $path);
      mkdir($backup, 0777, true);

      foreach ($json as $item) {
        if(!file_exists("$backup/" . $item["folder"])){
          mkdir("$backup/" . $item["folder"], 0777, true);
        }
        file_put_contents("$backup/" .  $item["folder"] . $item["file"], $item["content"]);
      }
    }

    /* Functions below are private. */

    private static function folders($path){
      $array = [];
      $scandir = scandir($path);
      unset($scandir[array_search('.', $scandir, true)]);
      unset($scandir[array_search('..', $scandir, true)]);

      if(count($scandir) < 1){
        return;
      }
      foreach($scandir as $file){
        if(is_dir($path.'/'.$file)){
          array_push($array, self::folders($path . '/' . $file));
        } else {
          array_push($array, [$path. "/", $file]);
        }
      }
      return $array;
    }

    private static function strings($array){
      $temp = [];
      foreach ($array as $value) {
        if(is_array($value[0])){
          $recursive = self::strings($value);
          foreach ($recursive as $r) {
            array_push($temp, [$r[0], $r[1]]);
          }
        } else {
          array_push($temp, [$value[0], $value[1]]);
        }
      }
      return $temp;
    }

    /* Functions below are for encryption and decryption. */

    private static function sign($string, $key) {
      return hash_hmac('sha256', $string, $key) . $string;
    }
    private static function verify($bundle, $key) {
      return hash_equals(
        hash_hmac('sha256', mb_substr($bundle, 64, null, '8bit'), $key),
        mb_substr($bundle, 0, 64, '8bit')
      );
    }

    public static function encrypt($string, $key){
      return base64_encode(
        openssl_encrypt($string, self::METHOD, $key, false, self::IV)
      );
    }
    public static function decrypt($string, $key){
      return openssl_decrypt(
        base64_decode($string), self::METHOD, $key, false, self::IV
      );
    }

    /* Functions below are just to add small functionality */

    private static function random($lenght = 8){
      $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $charsetLenght = strlen($charset);
      $string = NULL;
      for ($i = 0; $i < $lenght; $i++) {
          $string .= $charset[rand(0, $charsetLenght - 1)];
      }
      return $string;
    }
  }
?>
