<?php

// Exibir erros da api
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importações necessárias para a validação de email
require_once ('PHPMailer-master/src/PHPMailer.php');
require_once ('PHPMailer-master/src/SMTP.php');
require_once ('PHPMailer-master/src/Exception.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Variáveis de acesso
$host = "roundhouse.proxy.rlwy.net";
$user = "root";
$pass = "IfAZWSOZCqjzqbLDeusbseFmefDiFSeO";
$bd = "railway";
$porta = "27020";

// Executar conexão
$conectar = @mysqli_connect($host,$user,$pass,$bd,$porta);

// Verificar se a conexão foi bem sucedida
if (!$conectar) {
    die ("erro ".mysqli_connect_error());
}

// Tratando requisição de cadastro
if (isset($_POST['cadastro'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['cadastro'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $nome = $parametrosDivididos[0];
    $email = $parametrosDivididos[1];
    $senha = $parametrosDivididos[2];
    $dataNasc = $parametrosDivididos[3];
    $dataAtual = $parametrosDivididos[4];
    $imgPerfil = $parametrosDivididos[5];

    // Verificar se o email inserido já existe na base de dados
    $sqlVerificarDuplicacaoEmail = "SELECT * FROM Usuario WHERE Email_Responsavel = ?";
    
    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sqlVerificarDuplicacaoEmail);

    // Verificação de SLQ Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("s", $email);
        $verificarInjection->execute();
        $resultadoEmail = $verificarInjection->get_result();
     
        // Caso haja mais de um registro com o mesmo email
        if ($resultadoEmail->num_rows >= 1) {
            echo "email duplicado";
        }
        // Se não houver, adiciona o novo usuário ao banco
        else {
            $sql = "INSERT INTO Usuario(Nome_Usuario,Email_Responsavel,Senha,Dt_Nascimento,Dt_Cadastro,Ft_Perfil,Total_Pontuacao,Id_Astro) VALUES ('$nome','$email','$senha','$dataNasc','$dataAtual','$imgPerfil',0,1)";
            $verificarInjection = $conectar->prepare($sql);

            //Prevenção do SQL Injection
            if ($verificarInjection) {
                $verificarInjection->execute();
                $resultadoCadastro = $verificarInjection->get_result();

                if ($resultadoCadastro) {
                    echo "inserido";
                }
                else {
                    echo "naoInserido";
                }
                $verificarInjection->close();
            }
        }
        $verificarInjection->close();
    } else {
        echo "erro para verificar injecao";
    }
}

// Tratando requisição de login
if (isset($_POST['login'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['login'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $email = $parametrosDivididos[0];
    $senha = $parametrosDivididos[1];

    // Buscando o usuário no banco conforme as credenciais inseridas
    $sql = "SELECT * FROM Usuario WHERE Email_Responsavel = ? AND Senha = ?";

    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

    // Verificação de SLQ Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("ss", $email, $senha);
        $verificarInjection->execute();
        $resultado = $verificarInjection->get_result();

        // Obter os dados do usuário caso encontrado
        if ($resultado->num_rows > 0) {
            $linha = $resultado->fetch_assoc();
            $nomeUsuario = $linha['Nome_Usuario'];
            $imgUsuario = $linha['Ft_Perfil'];
            $senha = $linha['Senha'];
            $dtNascimento = $linha['Dt_Nascimento'];
            $pontuacao = $linha['Total_Pontuacao'];
            $dtCadastro = $linha['Dt_Cadastro'];
            $astroAtual = $linha['Id_Astro'];
            $nivelAtual = $linha['Nivel_Atual'];
            echo "logado####$nomeUsuario####$imgUsuario####$senha####$dtNascimento####$pontuacao####$dtCadastro####$astroAtual####$nivelAtual";
        } else {
            echo "senha invalida";
        }
        $verificarInjection->close();
    } else {
        echo "erro para verificar injecao";
    }
}

//Verificação se o e-mail já foi cadastrado anteriormente
if (isset($_POST['verificarExistenciaEmail'])) {
    // Armazenando os dados contidos na requisição
    $email = $_POST['verificarExistenciaEmail'];

    // Buscando o usuário no banco conforme as credenciais inseridas
    $sql = "SELECT * FROM Usuario WHERE Email_Responsavel = ?";

    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

    // Verificação de SLQ Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("s", $email);
        $verificarInjection->execute();
        $resultado = $verificarInjection->get_result();

        // Enviar uma mensagem caso o e-mail seja encontrado
        if ($resultado->num_rows > 0) {
            echo "existe";
        } else {
            echo "inexistente";
        }
    }
}

// Tratando requisição de alterar senha
if (isset($_POST['alterarSenha'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['alterarSenha'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $email = $parametrosDivididos[0];
    $senha = $parametrosDivididos[1];

    //Atualização da senha no banco
    $sql = "UPDATE Usuario SET Senha = ? WHERE Email_Responsavel = ?";

    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

    // Verificação de SLQ Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("ss",$senha,$email);
        if ($verificarInjection->execute()) {
            // Verificação se a senha foi alterada
            if ($verificarInjection->affected_rows > 0) {
                echo "alterado";
            }
            else {
                echo "inalterado";
            }
        }
        $verificarInjection->close();
    }
}

// Tratando da verificação de e-mail do usuário
if (isset($_POST['verificarEmail'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['verificarEmail'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $nomeUsuario = $parametrosDivididos[0];
    $email = $parametrosDivididos[1];
    $cod1 = $parametrosDivididos[2];
    $cod2 = $parametrosDivididos[3];
    $cod3 = $parametrosDivididos[4];
    $cod4 = $parametrosDivididos[5];

    // Uso da biblioteca PHPMailer para o envio de e-mail
    $mail = new PHPMailer(true);
    try {
        // Definição de variáveis
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'astroguidegroup@gmail.com'; 
        $mail->Password = 'farkppzhndyknioc'; 
        $mail->Port = 587;

        // Envio do e-mail ao usuário
        $mail->setFrom('astroguidegroup@gmail.com');
        $mail->addAddress($email);

        // Estilização do corpo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo ao AstroGuide';
        $mail->Body = "<html><body>";
        $mail->Body .= "<style> * { padding: 0; margin: 0; font-family: Verdana, Geneva, Tahoma, sans-serif; } header { width: 100%; height: 30vw; background-color: #1f1f1f; color: #fff; display: flex; justify-content: center; align-items: center; } h2,b,p { color: #000; text-align: center; font-size: 2em; } body { display: flex; justify-content: center; align-items: center; flex-direction: column; gap: 5vw; } </style>";
        $mail->Body .= "<header><h1>$nomeUsuario, bem vindo ao AstroGuide!</h1></header>";
        $mail->Body .= "<main><h2>Seu código de verificação é:</h2>";
        $mail->Body .= "<p><b>$cod1 $cod2 $cod3 $cod4</b></p>";
        $mail->Body .= "</main></body></html>";

        // Verificação do envio do e-mail
        if ($mail->send()){
            echo "Enviado com sucesso";
        }
        else {
            echo "Email não enviado";
        }
    }
    catch (Exception $e){
        echo "Mensagem não enviada: {$mail->ErrorInfo}";
    }
}

// Listar os quizzes
if (isset($_POST['quiz'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['quiz'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $nivel = $parametrosDivididos[0];
    $idAstro = $parametrosDivididos[1];

    // Variáveis que receberão as perguntas, alternativas e quais as alternativas corretas
    $arrPerguntas = array();
    $arrAlternativas = array();
    $arrAlternativasCertas = array();

    // Listar os quiz do astro e do nível em que o usuário está
    $sql = "SELECT * FROM Quiz WHERE Nivel = $nivel AND Id_Astro = $idAstro";

    // Executar o código SQL
    $quizEncontrado = mysqli_query($conectar,$sql);

    // Armazenar a linha em que o quiz foi encontrado
    $row = mysqli_fetch_assoc($quizEncontrado);

    // Obter a identificação do quiz
    $idQuiz = $row['Id_Quiz'];

    // Listar todas as perguntas do quiz
    $sql2  = "SELECT * FROM Pergunta WHERE Id_Quiz = $idQuiz";

    // Executar a busca
    $perguntasEncontradas = mysqli_query($conectar,$sql2);

    // Caso a busca tenha sido feita com sucesso
    if ($perguntasEncontradas) {
        // Buscar cada pergunta, alternativa e alternativa correta e armazenar nas arrays
        mysqli_data_seek($perguntasEncontradas, 0);
        while ($row = mysqli_fetch_assoc($perguntasEncontradas)) {
            array_push($arrPerguntas, $row['Texto']);
            array_push($arrAlternativas, $row['alternativa_1'], $row['alternativa_2'],$row['alternativa_3']);
            array_push($arrAlternativasCertas, $row['Alternativa_Certa']);
        }

        // Separar cada palavra e cada significado pelos caracteres "]"
        $stringPerguntas = implode("]",$arrPerguntas);
        $stringAlternativas = implode("]",$arrAlternativas);
        $stringAlternativasCertas = implode("]",$arrAlternativasCertas);
        echo "$stringPerguntas@@@@$stringAlternativas@@@@$stringAlternativasCertas";      
    }
}

// Alterar nome do usuário
if (isset($_POST['alterarNome'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['alterarNome'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $email = $parametrosDivididos[0];
    $nome = $parametrosDivididos[1];

    // Atualização do nome do usuário no banco
    $sql = "UPDATE Usuario SET Nome_Usuario = ? WHERE Email_Responsavel = ?";

    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

     // Verificação SQL Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("ss",$nome,$email);
        if ($verificarInjection->execute()) {
            if ($verificarInjection->affected_rows > 0) {
                echo "alterado";
            }
            else {
                echo "inalterado";
            }
        }
    $verificarInjection->close();
    }
}

// Registrar que o usuário já completou um nível e desbloquear o próximo
if (isset($_POST['passarNivel'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['passarNivel'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $email = $parametrosDivididos[0];
    $nivel = $parametrosDivididos[1];
    $pontos = $parametrosDivididos[2];

    // Código SQL para atualizar o registro do usuário, aumentando o nível e a pontuação onde o email for igual ao do usuário
    $sql = "UPDATE Usuario SET Nivel_Atual = ?, Total_Pontuacao = ? WHERE Email_Responsavel = ?";
    
    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

    // Verificação SQL Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("sss",$nivel,$pontos,$email);
        if ($verificarInjection->execute()) {
            if ($verificarInjection->affected_rows > 0) {
                echo "alterado";
            }
            else {
                echo "inalterado";
            }
        }
    $verificarInjection->close();
    }
}

// Registrar que o usuário já completou todos os níveis de um astro e desbloquear o próximo
if (isset($_POST['passarAstro'])) {
    // Armazenando os dados contidos na requisição
    $parametros = $_POST['passarAstro'];

    // Dividindo os valores em variáveis diferentes
    $parametrosDivididos = explode("#|#", $parametros);
    $email = $parametrosDivididos[0];
    $astro = $parametrosDivididos[1];
    $nivel = $parametrosDivididos[2];

    // SQL para atualizar o astro e o nível, onde o email for igual ao inserido
    $sql = "UPDATE Usuario SET Id_Astro = ?, Nivel_Atual = ? WHERE Email_Responsavel = ?";

    
    // Previnir SQL Injection
    $verificarInjection = $conectar->prepare($sql);

    // Verificação de  SQL Injection
    if ($verificarInjection) {
        $verificarInjection->bind_param("sss",$astro,$nivel,$email);
        if ($verificarInjection->execute()) {
            if ($verificarInjection->affected_rows > 0) {
                echo "alterado";
            }
            else {
                echo "inalterado";
            }
        }
    $verificarInjection->close();
    }
}

// Buscar as palvras presentes no glossário
if (isset($_POST['buscarPalavras'])) {

    // Buscar as palavras dentro do banco de dados em ordem alfabética
    $sql = "SELECT * FROM Palavra ORDER BY LENGTH(Texto) ASC;";

    // Executar a consulta
    $palavrasEncontradas = mysqli_query($conectar,$sql);

    // Criar as arrays onde as palavras e os significados serão armazenados
    $arrPalavras = array();
    $arrSignificados = array();

    // Caso a busca seja feita com êxito
    if ($palavrasEncontradas) {
        // Buscar palavra por palavra e armazenar nas arrays
        mysqli_data_seek($palavrasEncontradas, 0);
        while ($row = mysqli_fetch_assoc($palavrasEncontradas)) {
            array_push($arrPalavras, $row['Texto']);
            array_push($arrSignificados, $row['Significado']);
        }

        // Separar cada palavra e cada significado pelos caracteres ">>"
        $stringPalavras = implode(">>",$arrPalavras);
        $stringSignificados = implode(">>",$arrSignificados);
        echo "$stringPalavras@@@@$stringSignificados";      
    }
}
?>
