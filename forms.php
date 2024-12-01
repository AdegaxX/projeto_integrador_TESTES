<?php
include_once('config.php');

// Configura o fuso horário do servidor
date_default_timezone_set('America/Sao_Paulo');

// Verifica se o horário atual é maior ou igual a 21 horas
$currentHour = (int) date('H');
if ($currentHour >= 21) {
    echo "<script>
        alert('O formulário não pode ser preenchido após as 21 horas.');
        window.location.href = 'forms.php';
    </script>";
    exit;
}

// Manipulação do envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $matricula = trim($_POST['matricula']);
    $interesse = isset($_POST['interesse']) ? 1 : 0;

    // Verifica se o usuário já existe
    $queryCheck = "SELECT * FROM usuarios WHERE matricula = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("s", $matricula);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "<script>
            alert('Usuário já cadastrado! Redirecionando para edição...');
            window.location.href = 'editar.php?matricula=$matricula';
        </script>";
        exit;
    } else {
        // Usuário não existe, insere novo registro
        $queryInsert = "INSERT INTO usuarios (nome, matricula, interesse) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($queryInsert);
        $stmtInsert->bind_param("ssi", $nome, $matricula, $interesse);

        if ($stmtInsert->execute()) {
            echo "<script>alert('Usuário cadastrado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar usuário.');</script>";
        }
        $stmtInsert->close();
    }

    $stmtCheck->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário RU Itapajé</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #282A36; }
        form { width: 300px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; background-color: #CCCCCC; border-radius: 8px; }
        input {width: 100%; margin-bottom: 10px; margin: 5px auto; padding: 10px;}
        button { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { background-color: #007bff; color: white; border: none; border-radius: 4px; }
        button:hover { background-color: #0056b3; }
        .carrossel { width: 100; margin: 20% auto; text-align: center; }
        .carrossel img { width: 100%; max-width: 300px; display: none; border-radius: 8px; }
    </style>
</head>
<body>
    <form action="forms.php" method="POST">
        <h2>Cadastro de Usuário</h2>
        <div>
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="number" name="matricula" placeholder="Matrícula" required>

            <div class="carrossel">
                <img id="current-cardapio" src="imagens/CARDAPIO1.png" alt="Cardápio 1">
            </div>
        </div>
        <label>
            <input type="checkbox" name="interesse"> Tenho interesse
        </label>
        <button type="submit">Cadastrar</button>
    </form>

    <script>
        // Função para calcular automaticamente o prox dia útil do forms:
        function calcularProximoDia() {
            const hoje = new Date();    // Dia atual
            let proximoDiaUtil = new Date(hoje);    // Atribui a data na variável

            // Avança para o próximo dia
            proximoDiaUtil.setDate(hoje.getDate() + 1);

            while (proximoDiaUtil.getDay() == 0 || proximoDiaUtil.getDay() == 6) {  // Range para os dias úteis
                proximoDiaUtil.setDate(proximoDiaUtil.getDate() + 1);
            }

            const diasSemana = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
            const diaSemana = diasSemana[proximoDiaUtil.getDay()];

            // Formata o dia e o mês com dois dígitos
            const diaMes = String(proximoDiaUtil.getDate()).padStart(2, '0');
            const mes = String(proximoDiaUtil.getMonth() + 1).padStart(2, '0');
            const ano = proximoDiaUtil.getFullYear();

            document.getElementById("data-dia-util").innerText = `${diaSemana}, ${diaMes}/${mes}/${ano}`;
        }

        window.onload = function() {
            calcularProximoDia();
            const dadosSalvos = JSON.parse(localStorage.getItem("dadosFormulario")) || {};

            if (dadosSalvos.nome) document.getElementById("nome").value = dadosSalvos.nome;
            if (dadosSalvos.matricula) document.getElementById("matricula").value = dadosSalvos.matricula;
            if (dadosSalvos.interesse) {
                document.getElementById("interesse").checked = dadosSalvos.interesse === "true";
            }
        };

        // Cardápio automatico:
        const cardapios = [
            "imagens/CARDAPIO1.png",
            "imagens/CARDAPIO2.png",
            "imagens/CARDAPIO3.png",
            "imagens/CARDAPIO4.png"
        ];

        //Calcula o cardápio:
        function getCardapioSemana() {
            const today = new Date();
            const firstDayOfYear = new Date(today.getFullYear(), 0, 1);
            const daysSinceStartOfYear = Math.floor((today - firstDayOfYear) / (1000 * 60 * 60 * 24));
            const weekNumber = Math.floor(daysSinceStartOfYear / 7); // semanas completas desde o inicio do ano
            return cardapios[weekNumber % cardapios.length];
        }

        // Atualiza o cardápio da semana no DOMINGO:
        function updateCardapio() {
            const cardapioImg = document.getElementById("current-cardapio");
            cardapioImg.src = getCardapioDaSemana();
        }

        // Inicia o formulario:
            window.onload = function() {
                updateCardapio();

                // Preenche os dados salvos, se houver:
                const dadosSalvos = JSON.parse(localStorage.getItem("dadosFormulario")) || {};
                if (dadosSalvos.nome) document.getElementById("nome").value = dadosSalvos.nome;
                if (dadosSalvos.matricula) document.getElementById("matricula").value = dadosSalvos.matricula;
                if (dadosSalvos.interesse) {
                    document.getElementById("interesse").checked = dadosSalvos === "true";
                }
            };
    </script>
</body>
</html>
