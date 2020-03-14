<?php

//需求:將訂便當網頁的驗證碼的加法算式跟總和分別在成功抓取後也一併寫入資料庫



//(1.)抓出驗證碼的加法及總和===========================================================================================
$cookie =  dirname(__FILE__)."/cookie.txt";//把cookie存起來
$login_url = "https://dinbendon.net/do/login";
$curl= curl_init();
curl_setopt($curl, CURLOPT_URL, $login_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '0');
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, '0');
curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); 
$html = curl_exec($curl);
curl_close($curl);

//抓認證碼加法的算式
preg_match('/\<td style="width: 6em;" class="alignRight">(.*)<\/td>/U', $html, $match);
$num=$match[1];
print_r($num);//將加法印出來  xx+xx= 或 xx加xx 等於

preg_match_all('/[0-9]{0,}/',$num,$single);//利用array_sum函數把數值全部相加
$code = array_sum($single[0]);
echo $code;//印出總和

//抓出每次變更表單變更action網址串
preg_match('/<form action="(.*)" id="signInPanel_signInForm" method="post">/U', $html, $ma);
$a=$ma[1];

//(2.)模擬提交表單//(把每次變更的網址串$a帶進來)===========================================================================================
$posturl = "https://dinbendon.net$a";
//要提交的資料//(把每次變更的認證碼總和$code帶進來)
$user="BBinMobile";

$pw="BBinMobile";
$post = "signInPanel_signInForm%3Ahf%3A0=&username=$user&password=$pw&result=$code&submit=%E7%99%BB%E5%85%A5";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $posturl);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '0');
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, '0');
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post);//提交方式為post
// curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
curl_exec($curl);
curl_close($curl);



//(2.)建立MySQL的資料庫連接 

//PDO 寫法 //資料庫連接的參數
$dbhost = 'localhost';
$dbuser = 'root';
$dbpasswd = '';
$dbname = 'db';
$dsn = "mysql:host=".$dbhost.";dbname=".$dbname;

try //連接資料庫
{
    $conn = new PDO($dsn,$dbuser,$dbpasswd);
    $conn->exec("SET CHARACTER SET utf8"); //設置的資料庫編碼
    $conn->query("TRUNCATE TABLE stores");//清空資料表
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "連接資料庫成功"."<br>"; //印出連接成功在網頁上
}
catch(PDOException $e)
{
    echo "資料庫連接失敗: ".$e->getMessage();
}

try //將資料寫入資料庫
{
  $sql  = "INSERT INTO `stores` (`num`,`code`,`user`,`pw`) VALUES('{$num}','{$code}','$user','$pw')";
//   $conn= $pdo->prepare($sql);
//   $conn->execute($data);
  $conn->query($sql);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "資料已成功寫入資料庫"."<br>";
  echo "<br>".'資料寫入時間:'.date("Y-m-d H:i:s",(time()+8*3600))."<br>";
}
catch(PDOException $e)
{
echo "資料寫入資料庫失敗: ".$e->getMessage();
}
$conn = NULL; //關掉資料庫
?>

