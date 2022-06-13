#!/usr/bin/php

<?php

    $imagens_tibia_generator = [];
    $commit = [];
    $tempo = strtotime('now');

    // tempo em minutos
    define('TIME_BETWEEN_COMMITS', 1);

    define('PLAYER_NAME', 'Flutter Studant');

    define('FILE_BACKUP', 'backup.json');
    // Path de imagens que são geradas automáticas pelo tibia (se configurar para permitir)
    // Up Lvl, Up Skill, Dead, Boss, Drop, etc..
    define('TIBIA_SCREENSHOTS', '/home/dev/.local/share/CipSoft GmbH/Tibia/packages/Tibia/screenshots/');

    $primeiraLeitura = explode(PHP_EOL,file_get_contents(FILE_BACKUP));

    $pathTibiaSite = '/home/aplicativos/programador.dev/img/tibia/';
    $nome_buneco = str_replace(' ', '-', strtolower(PLAYER_NAME));

    foreach($primeiraLeitura as $imagem){
        if(strpos($imagem, PLAYER_NAME) === false){
            continue;
        }

        $imagens_tibia_generator[$imagem] = $imagem;
    }

    function commit(){
        global $tempo;
        global $commit;
        
        if($tempo <= strtotime('now - '.TIME_BETWEEN_COMMITS.' minutes')){
            echo TIME_BETWEEN_COMMITS." minuto passou, vamos enviar o commit\n";
            $tempo = strtotime('now');
            
            $titulo = '-';
            $descricao = '-';
            
            $listaDescricao = [];
            if(is_array($commit) and count($commit) > 0){
                
                $titulo = 'feat('.PLAYER_NAME.'): add news photos generate for Tibia';
                foreach($commit as $key => $imagem){
                    if(strpos($imagem, 'LevelUp') !== false){
                        // Level UP
                        $listaDescricao['lvl'] = 'Level Up!';
                    }
                    if(strpos($imagem, 'SkillUp') !== false){
                        // Skill UP
                        $listaDescricao['skill'] = 'Skill Up!';
                    }
                    if(strpos($imagem, 'BestiaryEntryUnlocked') !== false){
                        // Iniciando Bestiary
                        $listaDescricao['bestiary'] = 'Matando monstros de Bestiario.';
                    }
                    if(strpos($imagem, 'HighestDamageDealt') !== false){
                        // Maior dano causado
                        $listaDescricao['bestdamage'] = 'Maior dano causado até esse momento.';
                    }
                    if(strpos($imagem, 'Achievement') !== false){
                        // Achievement
                        $listaDescricao['achievement'] = 'Novo Achievement.';
                    }
                }
          
                $descricao = implode("\n", $listaDescricao);
    
                echo "O titulo e descricao do commit ficou.\n";
                echo "$titulo\n";
                echo "$descricao\n";
                shell_exec('cd /home/aplicativos/programador.dev && git add . && git commit -m "'.$titulo.'" -m "'.$descricao.'" && git push 2>&1');
                $commit = [];
                return;
            }
            
            echo "Não será comitado nada, não há nada novo.\n";
            $commit = [];
        }
    }

    function sendImagensToPathSite($imagem){

        global $commit;
        global $imagens_tibia_generator;

        global $pathTibiaSite;
        global $nome_buneco;

        try {

            if(!is_dir($pathTibiaSite.PLAYER_NAME)){
                shell_exec("echo 'qwerty' | sudo -S mkdir $pathTibiaSite$nome_buneco 2>&1");
            }

            $imagemSha1 = $nome_buneco.'+'.date('dmY_His').'_'.sha1($imagem);
            file_put_contents($pathTibiaSite.$nome_buneco.'/'.$imagemSha1.'.png', file_get_contents(TIBIA_SCREENSHOTS.$imagem));
            $commit[$imagem] = $imagem;

            $imagens_tibia_generator[$imagem] = $imagem;
            file_put_contents(FILE_BACKUP, $imagem.PHP_EOL, FILE_APPEND);
            echo "Adicinado uma nova imagem.\n";

        } catch (\Exception $erro) {
            die($erro->getMessage());
        }

    }

    function salva($imagem){
        if(strpos($imagem, PLAYER_NAME) === false){
            return;
        }

        $backupimagens = file_get_contents(FILE_BACKUP);

        if(!is_file(TIBIA_SCREENSHOTS.$imagem)){
            echo "Ops, a imagem não existe.";
            return;
        }


        global $nome_buneco;
        global $pathTibiaSite;
        $imagemSha1 = $nome_buneco.'+'.date('dmY').'_'.sha1($imagem);
        $imagemSuposta = $pathTibiaSite.$nome_buneco.'/'.$imagemSha1.'.png';
        if(strpos($backupimagens, $imagem) === false and !is_file($imagemSuposta)){
            sendImagensToPathSite($imagem);
        }
    }
    
    if(is_dir(TIBIA_SCREENSHOTS)){

        echo "wathing Tibia Prints Generator...\n";
    
        while(true){

            $imagens = scandir(TIBIA_SCREENSHOTS);

            foreach($imagens as $file){

                if(strpos($file, '.png') !== false){
                    salva($file);
                }
            }

            commit();
    
            sleep(2);
            echo date('H:i:s')." - ping ...\n";
        }
    }