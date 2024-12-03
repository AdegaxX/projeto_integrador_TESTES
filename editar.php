<?php
include_once('config.php');

// Verifica se a matrícula foi fornecida corretamente
if (isset($_GET['matricula']) && !empty($_GET['matricula'])) {
    $matricula = trim($_GET['matricula']);

    // Busca o usuário no banco de dados
    $query = "SELECT * FROM usuarios WHERE matricula = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    } else {
        echo "<script>
            alert('Usuário não encontrado!');
            window.location.href = 'forms.php';
        </script>";
        exit; // Interrompe a execução para evitar erros
    }
} else {
    echo "<script>
        alert('Matrícula não fornecida. Por favor, tente novamente.');
        window.location.href = 'forms.php';
    </script>";
    exit; // Interrompe a execução para evitar erros
}

// Atualiza o campo de interesse
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $matricula = $_POST['matricula'];
    $interesse = isset($_POST['interesse']) ? 1 : 0;

    $queryUpdate = "UPDATE usuarios SET interesse = ? WHERE matricula = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("is", $interesse, $matricula);

    if ($stmtUpdate->execute()) {
        echo "<script>
            alert('Interesse atualizado com sucesso!');
            window.location.href = 'forms.php';
        </script>";
    } else {
        echo "<script>alert('Erro ao atualizar interesse.');</script>";
    }
    $stmtUpdate->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Interesse</title>
    <style>
        .brasao {
            width: 35%;
            max-width: 450px;
            display: block;
            margin: 0 auto;
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #282A36;
            color: black;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
        }

        form {
            width: auto;
            max-width:auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #CCCCCC;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 30px ;
        }

        input {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        label input {
            margin-right: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* checkbox: */
        .container {
            display: flex;
            justify-content: flex-start;
            padding: 0; 
            margin-bottom: 15px;
        }
        /* Label: Ajusta o alinhamento e espaçamento */
        .check-label {
            display: flex;
            align-items: center; 
            gap: 5px; 
            font-size: 12px;
            margin: 0; 
            padding: 0; 
        }
        /* Checkbox: Remove espaçamentos padrão do input */
        .check {
            margin: 0; 
            padding: 0; 
        }
    </style>
</head>
<body>
    
    <form action="editar.php?matricula=<?= htmlspecialchars($usuario['matricula'], ENT_QUOTES, 'UTF-8') ?>" method="POST">
        <img class="brasao" src="brasaoUFC.png" alt="brasaoUFC">
        
        <h2>Editar Interesse</h2>

        <input type="text" value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        
        <input type="text" name="matricula" value="<?= htmlspecialchars($usuario['matricula'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        
        <div class="container">    
            <label class="check-label">
                <input class="check" type="checkbox" name="interesse" <?= $usuario['interesse'] == 1 ? 'checked' : '' ?>> <span>Tenho interesse</span>
            </label>
        </div>

        <button type="submit">Salvar edição</button>
    
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

        document.getElementById("meuFormulario").addEventListener("submit", function(event) {
            const hora = new Date();
            const horaAtual = hora.getHours();

            if (horaAtual >= 21) {
                alert("O formulário só pode ser enviado até as 21h.")
                event.preventDefault(); // cancela o envio do formulario
            }
        });
    
    </script>
</body>
</html>
