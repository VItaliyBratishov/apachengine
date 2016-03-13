<?php
	
	if ($handle = opendir('/var/www/apachengine/')) {
	    while (false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") {
				if ($file != "index.php" && $file != "info.php") unlink($file);
			}
		}
	}

	if (isset($_POST['deletedomain'])) {
		$domain = trim($_POST['deletedomain']);
		$password = trim($_POST['password']);
		unset($_POST);

		$findme = '127.0.0.1'.str_replace(" ","", $domain);
		
		$stream = "";
		$handle = @fopen("/etc/hosts", "r");
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) !== false) {
		        $mystring = str_replace(array(' ', "\n"), "", $buffer);

		        if ($mystring !== $findme){
		       		$stream .= $buffer;
		        } 
		    }
		    fclose($handle);
		}

		echo $domain;

		file_put_contents("hosts", $stream);
		chmod("hosts", 0755);

		file_put_contents("/var/www/apachengine/.secret", $password);
		chmod("/var/www/apachengine/.secret", 0777);

		$sh = "#!/bin/bash\n";
		$sh .= "sudo sudo -S mv hosts /etc/ < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S a2dissite $domain.conf < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S rm -rf /etc/apache2/sites-available/$domain.conf < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S rm -rf /var/www/$domain/ < /var/www/apachengine/.secret\n";
		$sh .= "sudo service apache2 restart\n";

		file_put_contents($domain."-del.sh", $sh);
		chmod($domain."-del.sh", 0777);

		exec("sudo -S sh $domain-del.sh < /var/www/apachengine/.secret", $output);

		
		exit;

    }

	if (isset($_POST['createdomain'])) {
		$domain = trim($_POST['createdomain']);
		$password = trim($_POST['password']);
		$drop = trim($_POST['drop']);
		unset($_POST);

		mkdir("/var/www/".$domain, 0777);

		if ($drop != ".") {

			mkdir("/var/www/".$domain."/".$drop, 0777);
			file_put_contents("/var/www/".$domain."/".$drop."/index.php", "<?php echo 'Локальный домен $domain с поддержкой PHP'; ?>");
			chmod("/var/www/".$domain."/index.php", 0777);

		} else {

			file_put_contents("/var/www/".$domain."/index.php", "<?php echo 'Локальный домен $domain с поддержкой PHP'; ?>");
			chmod("/var/www/".$domain."/index.php", 0777);

		}

		file_put_contents("/var/www/apachengine/.secret", $password);
		chmod("/var/www/apachengine/.secret", 0777);

		if ($drop == ".") $drop = "";

		$conf = "<VirtualHost *:80>\n"
		        ."\tServerName $domain\n"
		        ."\tServerAdmin webmaster@localhost\n"
		        ."\tDocumentRoot /var/www/$domain/$drop\n"
		     ."</VirtualHost>\n";

		file_put_contents($domain.".conf", $conf);
		chmod($domain.".conf", 0777);

		$sh = "#!/bin/bash\n";
		$sh .= "sudo sudo -S  mv $domain.conf /etc/apache2/sites-available/ < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S echo \"127.0.0.1  $domain\" >> /etc/hosts < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S a2ensite $domain.conf < /var/www/apachengine/.secret\n";
		$sh .= "sudo -S chmod -R 777 * /var/www/$domain/  < /var/www/apachengine/.secret\n";
		$sh .= 'sudo -S chown -R $USER:$USER /var/www/'. $domain . '/ < /var/www/apachengine/.secret'."\n";
		$sh .= "sudo service apache2 restart\n";

		file_put_contents($domain.".sh", $sh);
		chmod($domain.".sh", 0777);

		exec("sudo -S sh $domain.sh < /var/www/apachengine/.secret", $output);

		$a = unlink($domain.".sh");
		unlink($domain.".conf");
		unlink(".secret");

		echo "Успешно добавлен новый хост";
		exit;
	}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://code.jquery.com/jquery-2.2.1.min.js" integrity="sha256-gvQgAFzTH6trSrAWoH1iPo9Xc96QxSZ3feW6kem+O00=" crossorigin="anonymous"></script>
  <title>Управление сервером</title>

  <style>
	:root,body{font-family:'Open Sans','Helvetica Neue',Helvetica,'Lucida Grande',sans-serif}h1,h2,h3{font-weight:500}body,h5{font-size:1.6rem}h4,h5,h6{font-weight:600}a,a:focus,a:hover{text-decoration:none}blockquote,pre{margin:1.6rem 0}blockquote,figcaption{font-family:Georgia,Times,'Times New Roman',serif}footer,section{max-width:100%;clear:both;float:left}article,aside,dl,hr,section{margin-bottom:1.6rem}footer,hr{border-top:.1rem solid rgba(0,0,0,.2)}footer,img,section{max-width:100%}img,select[multiple]{height:auto}hr,legend{width:100%}fieldset,legend{padding:.8rem 0}audio,canvas,iframe,img,input[type=radio],input[type=checkbox],svg,textarea,video{vertical-align:middle}pre,textarea{overflow:auto}legend,ol,textarea,ul{margin-bottom:.8rem}*,body{padding:0}::after,::before,td,th{vertical-align:inherit}footer,nav ul,td,th{text-align:center}:root{box-sizing:border-box;cursor:default;line-height:1.4;-ms-overflow-style:-ms-autohiding-scrollbar;overflow-y:scroll;text-rendering:optimizeLegibility;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;text-size-adjust:100%}[hidden],audio:not([controls]),template{display:none}details,main,summary{display:block}input[type=number]{width:auto}input[type=search]{-webkit-appearance:textfield}input[type=search]::-webkit-search-cancel-button,input[type=search]::-webkit-search-decoration{-webkit-appearance:none}progress{display:inline-block}small{font-size:75%;color:#777}big{font-size:125%}[unselectable]{user-select:none}[unselectable],button,input[type=submit]{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none}*,::after,::before{border-style:solid;border-width:0;box-sizing:inherit}*{font-size:inherit;line-height:inherit;margin:0}::after,::before{text-decoration:inherit}a{color:#1271db;-webkit-transition:.25s ease;transition:.25s ease}button,input,select,textarea{background-color:transparent;border:.1rem solid #ccc;color:inherit;font-family:inherit;font-style:inherit;font-weight:inherit;min-height:1.4em}input:not([type]),select{background-color:#fff}code,kbd,pre,samp{font-family:Menlo,Monaco,Consolas,'Courier New',monospace,monospace}code,pre{color:#444;background:#efefef;font-family:Menlo,Monaco,Consolas,'Courier New',monospace;font-size:1.4rem;word-break:break-all;word-wrap:break-word}::-moz-selection{background-color:#b3d4fc;text-shadow:none}::selection{background-color:#b3d4fc;text-shadow:none}button::-moz-focus-inner{border:0}@media screen{[hidden~=screen]{display:inherit}[hidden~=screen]:not(:active):not(:focus):not(:target){clip:rect(0 0 0 0)!important;position:absolute!important}}hr,legend,main,pre,textarea{display:block}article,aside,button,footer,input:not([type]),input[type=submit],section{display:inline-block}body{color:#444;font-style:normal;font-weight:400}p{margin:0 0 1.6rem}h1,h2,h3,h4,h5,h6{font-family:'Lato','Open Sans','Helvetica Neue',Helvetica,'Lucida Grande',sans-serif;margin:2rem 0 1.6rem}h3,h4,h5,h6{font-style:normal;margin:1.6rem 0 .4rem}h1{border-bottom:.1rem solid rgba(0,0,0,.2);font-size:3.6rem;font-style:normal}h2{font-size:3rem;font-style:normal}h3{font-size:2.4rem}h4{font-size:1.8rem}h6{color:#777;font-size:1.4rem}pre{padding:1.6rem}dd{margin-left:4rem}ol,ul{padding-left:2rem}blockquote{border-left:.2rem solid #1271db;font-style:italic;padding-left:1.6rem}html{font-size:62.5%}article,aside,details,footer,header,main,section,summary{display:block;height:auto;margin:0 auto;width:100%}main{margin:0 auto;max-width:76.8rem;padding:0 1.6rem 1.6rem}article{clear:left;float:left;max-width:calc(60% - 1rem)}aside{clear:right;float:right;max-width:calc(40% - 1rem)}footer{padding:1rem 0}img{vertical-align:baseline}@media screen and (max-width:40rem){article,aside,section{clear:both;display:block;max-width:100%}img{margin-right:1.6rem}}input[type=password],input[type=email],input[type=url],input[type=date],input[type=month],input[type=time],input[type=datetime],input[type=datetime-local],input[type=week],input[type=tel],input[type=color],input[type=number],input[type=search],input[type=text],select{border:.1rem solid #ccc;border-radius:0;display:inline-block;padding:.8rem;vertical-align:middle}input:not([type]){-webkit-appearance:none;background-clip:padding-box;border-radius:0;color:#444;padding:.8rem;text-align:left}input:not([type]),select,textarea{border:.1rem solid #ccc}input[type=color]{padding:.8rem 1.6rem}input:not([type]):focus,input[type=password]:focus,input[type=email]:focus,input[type=url]:focus,input[type=date]:focus,input[type=month]:focus,input[type=time]:focus,input[type=datetime]:focus,input[type=datetime-local]:focus,input[type=week]:focus,input[type=tel]:focus,input[type=color]:focus,input[type=number]:focus,input[type=search]:focus,input[type=text]:focus,select:focus,textarea:focus{border-color:#b3d4fc}input[type=radio]:focus,input[type=checkbox]:focus,input[type=file]:focus{outline:thin solid .1rem}input:not([type])[disabled],input[type=password][disabled],input[type=email][disabled],input[type=url][disabled],input[type=date][disabled],input[type=month][disabled],input[type=time][disabled],input[type=datetime][disabled],input[type=datetime-local][disabled],input[type=week][disabled],input[type=tel][disabled],input[type=color][disabled],input[type=number][disabled],input[type=search][disabled],input[type=text][disabled],select[disabled],textarea[disabled]{background-color:#efefef;color:#777;cursor:not-allowed}input[readonly],select[readonly],textarea[readonly]{background-color:#efefef;border-color:#ccc;color:#777}input:focus:invalid,select:focus:invalid,textarea:focus:invalid{border-color:#e9322d;color:#b94a48}button a,input[type=submit] a,legend{color:#444}input[type=radio]:focus:invalid:focus,input[type=checkbox]:focus:invalid:focus,input[type=file]:focus:invalid:focus{outline-color:#ff4136}label{line-height:2}fieldset{border:0;margin:0}legend{border-bottom:.1rem solid #ccc}textarea{resize:vertical;border-radius:0;padding:.8rem}button,input[type=submit]{background-color:transparent;border:.2rem solid #444;border-radius:0;color:#444;cursor:pointer;margin-bottom:.8rem;margin-right:.4rem;padding:.8rem 1.6rem;text-align:center;text-transform:uppercase;transition:.25s ease;-webkit-user-drag:none;user-select:none;vertical-align:baseline}button,input[type=submit],nav a{text-decoration:none;-webkit-transition:.25s ease}button::-moz-focus-inner,input[type=submit]::-moz-focus-inner{padding:0}button:hover,input[type=submit]:hover{background:#444;border-color:#444;color:#fff}button:active a,button:hover a,input[type=submit]:active a,input[type=submit]:hover a{color:#fff}button:active,input[type=submit]:active{background:#6a6a6a;border-color:#6a6a6a;color:#fff}button:disabled,input[type=submit]:disabled{box-shadow:none;cursor:not-allowed;opacity:.4}nav ul{list-style:none;margin:0;padding:1.6rem 0 0}nav a,td,th{padding:.8rem 1.6rem}nav ul li{display:inline}nav a{border-bottom:.2rem solid transparent;color:#444;transition:.25s ease}nav a:hover{border-color:rgba(0,0,0,.2)}nav a:active{border-color:rgba(0,0,0,.56)}table{border-collapse:collapse;border-spacing:0;margin-bottom:1.6rem}caption{padding:.8rem 0}thead th{background:#efefef;color:#444}tr{background:#fff;margin-bottom:.8rem}td,th{border:.1rem solid #ccc}tfoot tr{background:0 0}tfoot td{color:#efefef;font-size:.8rem;font-style:italic;padding:1.6rem .4rem}

	input.input {width: 686px; border: .2rem solid #444; margin-bottom: 1px;}
	select {border: .2rem solid #444; margin-bottom: 1px;}
	main {max-width: 100.8rem;}
	sup {font-size: 10px;}
	aside {max-width: calc(35% - 1rem);}
	
	.mainmodal, .delmodal {
		background: rgba(0,0,0, .5);
		width: 100%;
		height: 100%;
		position: fixed;
		display: none
	}
	.modalpas, .delpas {
		width: 350px;
		height: 120px;
		background: #fff;
		border: 2px solid #fff;
		box-shadow: 0px 0px 2px #000;
		position: fixed;
		left:50%;
		top:50%;
		margin-top: -150px;
		margin-left: -175px;
		z-index:10;
		padding: 10px;
	}

	.modalpas input, .delpas input {
		border: .2rem solid #444; margin-bottom: 1px;
	}

	.modalpas img, .delmodal img {
		vertical-align: baseline;
		width: 60%;
		height: 60%;
		text-align: center;
		margin: 0 auto;
		display: block;
	}

  </style>
</head>

<body>
	<div class="mainmodal">
		 <div class="modalpas">
			<p>Введите пароль администратора:</p>
			<input class="inputpas" type="password" placeholder="*******">
			<button class="okpas">Готово</button>
		</div>
	</div>

	<div class="delmodal">
		 <div class="delpas">
			<p>Введите пароль администратора:</p>
			<input class="delinputpas" type="password" placeholder="*******">
			<button class="delokpas">Готово</button>
		</div>
	</div>
   

  <main>
    <!-- Navigation -->
    <nav>
      <ul>
        <li><a href="#"><b>Главная</b></a></li>
        <li><a href="https://github.com/splincode/apachengine" target="blank">Github</a></li>
      </ul>
    </nav>
	<br>

	<section>
		<h1>Создание домена</h1>
		<form action="/index.php" method="post" target="blank">

			<select class="drop" name="drop" id="drop">
				<option value=".">Корневая папка:</option>
				<option value=".">текущая</option>
			    <option value="public/">public/</option>
			    <option value="www/">www/</option>
			</select>
			<input class="input" type="text" placeholder="Название, например test.ru" name="createdomain">
			<button class="addhost">Создать</button>
		</form>
		<script>
			var $pass, $domain, $dir;

			$('.addhost').click(function(){
				$inp = $('.input');
				$domain = $inp.val();
				$('.mainmodal').fadeIn();
				$sel = $('.drop option:selected').val();

				return false;
			});

			$('.okpas').click(function(){
				$inp = $('.inputpas');
				$pass = $inp.val(); $inp.val("");

				$.ajax({
				  type: "POST",
				  data: "createdomain="+$domain+"&password="+$pass+"&drop="+$sel,
				  url: "index.php",
				  success: function(a){
				  }
				});

				$('.modalpas').html("<p align='center'>Хост инициализирован</p><img src='https://upload.wikimedia.org/wikipedia/commons/4/45/Apache_HTTP_server_logo_(2016).png'>");
				setTimeout(function(){
					location.reload();
				}, 3000);

				return false;
			});

		</script>
	</section>
    <!-- article -->
    <article>
      <h1>Домены</h1>
	
	
	  <table>
	    <thead>
	      <tr>
		<th>Хост</th>
		<th>Просмотр</th>
		<th>Директория</th>
		<th>Удалить</th>
	      </tr>
	    </thead>
	    <tbody>
	     
	     <?php

	     	echo "<tr>";
	     	echo "<td>localhost</td>";
	     	echo "<td><a href='#'>перейти</a></td>";
	     	echo "<td><a href='#'>/var/www/html/</a></td>";
	     	echo "<td><a href='#'>-</a></td>";
	     	echo "</tr>";

	     
	     	if ($handle = opendir('/var/www/')) {

	     	    /* Именно этот способ чтения элементов каталога является правильным. */
	     	    while (false !== ($file = readdir($handle))) {
	     			if($file != "." && $file != "..") {

	     				if(
	     					$file != ".sudo_as_admin_successful" && 
	     					$file !=  "localhost" &&
	     					$file !=  "phpmyadmin" &&
	     					$file !=  "apachengine" &&
	     					$file !=  "html"
	     				) {

	     					echo "<tr>";
	     					echo "<td>$file</td>";
	     					echo "<td><a href='http://$file' target='blank'>перейти</a></td>";
	     					echo "<td><a href='#'>/var/www/$file/</a></td>";
	     					if ($file != "localhost") {
	     						echo "<td><a class='deletedomain' href='".$file."'>да</a></td>";
	     					} else echo "<td><a href='#'>-</a></td>";
	     					echo "</tr>";

	     				}

	     			}
	     	    }

	     	    closedir($handle); 
	     	}

	     ?>
		
		  <script>

		    var $deldomain;
		  	$('.deletedomain').click(function(){
		  		$deldomain = $(this).attr("href");
		  		$('.delmodal').fadeIn();
		  		return false;
		  	});

		  	$('.delokpas').click(function(){
		  		$inp = $('.delinputpas');
		  		$pass = $inp.val(); $inp.val("");

		  		$.ajax({
		  		  type: "POST",
		  		  data: "deletedomain="+$deldomain+"&password="+$pass,
		  		  url: "index.php",
		  		  success: function(a){
		  		  	
		  		  }
		  		});

		  		$('.delpas').html("<p align='center'>Хост удален</p><img src='https://upload.wikimedia.org/wikipedia/commons/4/45/Apache_HTTP_server_logo_(2016).png'>");

		  		setTimeout(function(){
		  			location.reload();
		  		}, 3000);

		  		return false;
		  	});


		  </script>

	    </tbody>
	  </table>



    </article>

    <!-- aside -->
    <aside>
      <h1>Дополнительно</h1>

      <a href="/phpmyadmin" target="_blank"><button style="width:100%">Перейти в phpMyAdmin</button></a> <br>
      <a href="/info.php" target="_blank"><button style="width:100%">Посмотреть версию PHP</button></a>
    </aside>


    <!-- footer -->
    <footer>
      <p>&copy Apachengine 2016</p>
    </footer>
  </main>

</body>
</html>