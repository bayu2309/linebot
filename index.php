<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = getenv("catheroku");
$channel_secret = getenv("csheroku");

// inisiasi objek bot
//include 'codenya.php';
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$configs =  [
  'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
$bot->getProfile(userId);
$bot->getMessageContent(messageId);
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
  // get request body and line signature header
  $body        = file_get_contents('php://input');
  $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

  // log body and signature
  file_put_contents('php://stderr', 'Body: '.$body);

  if($pass_signature === false)
  {
    // is LINE_SIGNATURE exists in request header?
    if(empty($signature)){
      return $response->withStatus(400, 'Signature not set');
    }

    // is this request comes from LINE?
    if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
      return $response->withStatus(400, 'Invalid signature');
    }
  }

  // kode aplikasi nanti disini
  $data = json_decode($body, true);
  if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
      if ($event['type'] == 'message')
      {
        $userId     = $event['source']['userId'];
        $groupId     = $event['source']['groupId'];
        $getprofile = $bot->getProfile($userId);
        $profile    = $getprofile->getJSONDecodedBody();
        $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
        $a = (explode('-',$event['message']['text']));
        if($a[0] == "/help"){
          $phpnya="<?php\necho \"tulis aja disini kode phpnya\";";
          $carouselTemplateBuilder = new CarouselTemplateBuilder([
            new CarouselColumnTemplateBuilder("Menu", "Menu FoneBot","https://farkhan.000webhostapp.com/b1.jpg",[
              new MessageTemplateActionBuilder('SMS','/sms'),
              new MessageTemplateActionBuilder('SiamBot','/IPK'),
              new MessageTemplateActionBuilder('Jadwal Sholat','/jadwal'),
            ]),
            new CarouselColumnTemplateBuilder("Menu", "Menu FoneBot ","https://farkhan.000webhostapp.com/b1.jpg",[
              new MessageTemplateActionBuilder('PHP',$phpnya),
              new MessageTemplateActionBuilder('UserID','/userid'),
              new MessageTemplateActionBuilder('GroupID','/groupid'),
            ]),
            new CarouselColumnTemplateBuilder("Developer", "Farkhan Azmi Filkom UB","https://farkhan.000webhostapp.com/b2.jpg",[
              new UriTemplateActionBuilder('Line',"http://line.me/ti/p/~foneazm"),
              new UriTemplateActionBuilder('Github',"http://github.com/foneazmi/"),
              new UriTemplateActionBuilder('LinkedIn',"https://linkedin.com/in/farkhanazmi/"),
            ]),
          ]);
          $templateMessage = new TemplateMessageBuilder('Help FoneBot',$carouselTemplateBuilder);
          $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        }
        if ($a[0]=="/help2") {
          // $menu="/userid -> mendapatkan userId\n"
          // ."/groupid -> mendapatkan groupId\n"
          // //."/yt-keyword -> menampilkan video youtube\n"
          // ."/sms-nomortujuan-isipesan -> mengirimkan pesan gratis melalui bot\n"
          // ."/jadwal-wilayah -> melihat jadwal waktu Sholat\n"
          // ."/IPK-NIM -> Khusus Mahasiswa Brawijaya dapat melihat detail IP dan IPK :v\n"
          // ."==========================\n"
          // ."bot ini juga bisa menyimpan NOTE atau catatan kecil\n"
          // ."1.menambah note\n"
          // ."/tambah-nama note-detail note\n"
          // ."ex: /tambah-pemweb-kelas F2.2\n"
          // ."2.melihat semua note\n"
          // ."/semua\n"
          // ."3.melihat detail note\n"
          // ."/detail-nama note\n"
          // ."ex: /detail-pemweb\n"
          // ."4.menghapus note\n"
          // ."/hapus-nama note\n"
          // ."ex: /hapus-pemweb\n"
          // ."5. next update, melakukan sunting jadwal\n"
          // ."==========================\n"
          // ."terakhir\nbot ini juga dapat menjalankan kode program sederhana PHP\n"
          // ."langsung coba saja\n"
          // ."<?php\n"
          // ."echo \"test\";"
          // ."\n=========================="
          // ;
          $result = $bot->replyText($event['replyToken'], "/help2 sedang di rombak, silakan gunakan /help");
        }
        else if ($a[0]=="/userid") {
          $result = $bot->replyText($event['replyToken'], $userId);
        }
        else if ($a[0]=="/groupid") {
          $result = $bot->replyText($event['replyToken'], $event['source']['groupId']);
        }
        else if ($a[0]=="/sms") {
          if (isset($a[1])) {
            $xmlString = file_get_contents(getenv("smsapi").urlencode($a[1])."&pesan=".urlencode($a[2]));
            $xml = new SimpleXMLElement($xmlString);
            $hasil="SMS ke nomor ".$a[1].", Status : ".$xml->message->text;
          }else{
            $hasil="untuk menggunakan SMS gratis"
            ."\n/sms-nomortujuan-isipesan";
          }
          $result = $bot->replyText($event['replyToken'], $hasil);
        }
        else if ($a[0]=="/jadwal") {
          $kota=(isset($a[1])) ? $a[1] : "malang";
          $stored = file_get_contents("http://api.aladhan.com/v1/timingsByCity?city=$kota&country=indonesia&method=11");
          $datanya = json_decode($stored, TRUE);
          $jadwalsholat=$datanya['data']['timings'];
          $hijri=$datanya['data']['date'];
          $hasilnya="Jadwal Sholat \nWilayah ".$kota.", ".$hijri['readable']
          ."\n================"
          ."\nImsak : ".$jadwalsholat['Imsak']
          ."\nSubuh : ".$jadwalsholat['Fajr']
          ."\nDhuhur : ".$jadwalsholat['Dhuhr']
          ."\nAshar : ".$jadwalsholat['Asr']
          ."\nMaghrib : ".$jadwalsholat['Maghrib']
          ."\nIsha' : ".$jadwalsholat['Isha']
          ."\n================"
          ."\n".$hijri['hijri']['day']." ".$hijri['hijri']['month']['en']." ".$hijri['hijri']['year'];
          $hasilnya=(isset($a[1])) ? $hasilnya : $hasilnya."\n================\nuntuk wilayah lain gunakan\n/jadwal-namawilayah";
          $result = $bot->replyText($event['replyToken'],$hasilnya);
        }
        else if (substr($event['message']['text'],0,5)=='<?php') {
          $data = array(
            'php' => $event['message']['text']
          );
          $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
          $result = $bot->replyText($event['replyToken'], $babi);
        }
        else if($a[0]=="/IPK"){
          if (isset($a[1])) {
            include 'ScrapingSIAM/potong.php';
            $gg = new DataSiam();
            $hasil=$gg->get_data($a[1]);
          }else{
            $hasil="untuk menggunakan SiamBot"
            ."\n/IPK-nim";
          }

          $result = $bot->replyText($event['replyToken'],$hasil);
        }
        // // Coba
        // if($a[0] == "/coba1"){
        //   $buttonTemplateBuilder = new ButtonTemplateBuilder(
        //     "buttons",
        //     "title",
        //     "text",
        //     "https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg",
        //     [
        //       new MessageTemplateActionBuilder('Action Button','action'),
        //     ]
        //   );
        //   $templateMessage = new TemplateMessageBuilder('nama template', $buttonTemplateBuilder);
        //   $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        // }
        // if($a[0] == "/coba2"){
        //   $confirmTemplateBuilder = new ConfirmTemplateBuilder(
        //     "coba confirm?",
        //     [
        //       new MessageTemplateActionBuilder('Ya',"/ya"),
        //       new MessageTemplateActionBuilder('Tidak','/tidak'),
        //     ]
        //   );
        //   $templateMessage = new TemplateMessageBuilder('nama template', $confirmTemplateBuilder);
        //   $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        // }
        // if($a[0] == "/coba3"){
        //   $carouselTemplateBuilder = new CarouselTemplateBuilder([
        //     new CarouselColumnTemplateBuilder("title", "text","https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg",[
        //       new UriTemplateActionBuilder('buka',"http://hilite.me/"),
        //     ]),
        //     new CarouselColumnTemplateBuilder("title", "text","https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg",[
        //       new UriTemplateActionBuilder('Buka',"http://hilite.me/"),
        //     ]),
        //   ]);
        //   $templateMessage = new TemplateMessageBuilder('nama template',$carouselTemplateBuilder);
        //   $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        // }
        // if($a[0] == "/coba4"){
        //   $ImageCarouselTemplateBuilder = new ImageCarouselTemplateBuilder([
        //     new ImageCarouselColumnTemplateBuilder(
        //       "https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg",
        //       new UriTemplateActionBuilder(
        //         'Buka Browser',
        //         "https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg")
        //     ),
        //     new ImageCarouselColumnTemplateBuilder(
        //       "https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg",
        //       new UriTemplateActionBuilder(
        //         'Buka Browser',
        //         "https://i0.wp.com/angryanimebitches.com/wp-content/uploads/2013/03/tamakomarket-overallreview-tamakoanddera.jpg")
        //     ),
        //   ]);
        //   $templateMessage = new TemplateMessageBuilder('nama template',$ImageCarouselTemplateBuilder);
        //   $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        // }
        else if ($userId=="U4f3b524bfcd08556173108d04ae067ad") {
          // if ($a[0]=="/ktpkk") {
          //   $stored = file_get_contents('http://farkhan.000webhostapp.com/nutshell/read.php?AksesToken='.getenv("csheroku"));
          //   $obj = json_decode($stored, TRUE);
          //   $result = $bot->replyText($event['replyToken'], $obj['Data'][0]['nik_kk']);
          // }
          if ($a[0]=="/getdata" && $a[1]!="165150700111005") {
            $output=file_get_contents(getenv("apisiam").$a[1]);
            $datanya = (json_decode($output, true));
            $hasilnya="Detail Data Mahasiswa \nNIM ".$datanya['nim']
            ."\n================"
            ."\nNama : ".$datanya['nama']
            ."\nTTL : ".$datanya['ttl']
            ."\nAgama : ".$datanya['agama']
            ."\nFakultas : ".$datanya['fak']
            ."\nProdi : ".$datanya['prod']
            ."\nAngkatan : ".$datanya['ang']
            ."\nCluster : ".$datanya['clus']
            ."\n================";
            $result = $bot->replyText($event['replyToken'], $hasilnya);
          }
          else if ($a[0]=="/yt") {
            $yt=file_get_contents('https://www.youtube.com/results?search_query='.urlencode($a[1]));
            $plm=strpos($yt,'<a aria-hidden="true"')+29;
            $pla=strpos($yt,'"',$plm);
            $link=substr($yt, $plm,$pla-$plm);
            $hasilnya= "http://youtube.com".htmlspecialchars($link);
            $result = $bot->replyText($event['replyToken'], $hasilnya);
          }
        }
        if(
          $event['source']['type'] == 'group' or
          $event['source']['type'] == 'room'
        ){
          if($event['source']['userId']){
            if ($a[0]=="/tambah") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/storeData.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]).'&isi_jadwal='.urlencode($a[2]));
              $obj = json_decode($stored, TRUE);
              $result = $bot->replyText($event['replyToken'], $obj['message']);
            }
            else if ($a[0]=="/semua") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['groupId']);
              $datanya = json_decode($stored, TRUE);
              $hasilnya="Note Yang Disimpan";
              if (is_array($datanya) || is_object($datanyas)) {
                foreach ($datanya as $datanyas) {
                  echo $datanyas['jadwal'];
                  foreach($datanyas as $datanyass)
                  {
                    $hasilnya=$hasilnya."\n".$datanyass['nama_jadwal'];
                  }
                }
              }
              $result = $bot->replyText($event['replyToken'],$hasilnya);
            }else if ($a[0]=="/detail") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]));
              $datanya = json_decode($stored, TRUE);
              $hasilnya="Detail Note ".$a[1];
              if (is_array($datanya) || is_object($datanyas)) {
                foreach ($datanya as $datanyas) {
                  echo $datanyas['jadwal'];
                  foreach($datanyas as $datanyass)
                  {
                    $hasilnya=$hasilnya."\n".$datanyass['detail'];
                  }
                }
              }
              $result = $bot->replyText($event['replyToken'],$hasilnya);
            }else if ($a[0]=="/hapus") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/deleteNote.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]));
              $obj = json_decode($stored, TRUE);
              $result = $bot->replyText($event['replyToken'], $obj['message']);
            }
            return $res->withJson($result->getJSONDecodedBody(), $event['message']['text'].$result->getHTTPStatus());
          } else {
            if (substr($event['message']['text'],0,2)=='IP' & strlen($event['message']['text'])==18){
              $result = $bot->replyText($event['replyToken'], 'Add terlebih dahulu');
            }
            return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
          }
        } else {
          if($event['message']['type'] == 'text'){
            if ($a[0]=="/tambah") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/storeData.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]).'&isi_jadwal='.urlencode($a[2]));
              $obj = json_decode($stored, TRUE);
              $result = $bot->replyText($event['replyToken'], $obj['message']);
            }
            else if ($a[0]=="/semua") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['userId']);
              $datanya = json_decode($stored, TRUE);
              $hasilnya="Note Yang Disimpan";
              if (is_array($datanya) || is_object($datanyas)) {
                foreach ($datanya as $datanyas) {
                  echo $datanyas['jadwal'];
                  foreach($datanyas as $datanyass)
                  {
                    $hasilnya=$hasilnya."\n".$datanyass['nama_jadwal'];
                  }
                }
              }
              $result = $bot->replyText($event['replyToken'],$hasilnya);
            }else if ($a[0]=="/detail") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]));
              $datanya = json_decode($stored, TRUE);
              $hasilnya="Detail Note ".$a[1];
              if (is_array($datanya) || is_object($datanyas)) {
                foreach ($datanya as $datanyas) {
                  echo $datanyas['jadwal'];
                  foreach($datanyas as $datanyass)
                  {
                    $hasilnya=$hasilnya."\n".$datanyass['detail'];
                  }
                }
              }
              $result = $bot->replyText($event['replyToken'],$hasilnya);
            }else if ($a[0]=="/hapus") {
              $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/deleteNote.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]));
              $obj = json_decode($stored, TRUE);
              $result = $bot->replyText($event['replyToken'], $obj['message']);
            }

          }
        }
      }
    }
  }

});
$app->get('/profile/{userId}', function($req, $res) use ($bot)
{
  $route  = $req->getAttribute('route');
  $userId = $route->getArgument('userId');
  $result = $bot->getProfile($userId);
  return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});
$app->run();
