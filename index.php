<!DOCTYPE html>
<html lang="pt-BR"> <!-- Dizendo para o navegador que a página está em português do Brasil -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tarefas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"
        integrity="sha512-u3fPA7V8qQmhBPNT5quvaXVa1mnnLSXUep5PS1qo5NRzHwG19aHmNJnj1Q8hpA/nBWZtZD4r4AX6YOt5ynLN2g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
        integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<?php
$arquivo_txt = 'dados/tarefas.txt'; // Caminho para o arquivo TXT

//definindo ação padrão
$acao = 'listar';
if (isset($_GET['a'])) {
    $acao = $_GET['a']; //mudando ação definida antes, se for o caso
}

$linha = 0; //definindo linha padrão para edição ou exclusão 
if (isset($_GET['l'])) {
    $linha = intval($_GET['l']); //mudando linha para edição ou exclusão, se for o caso
}

switch ($acao) {
    case 'inserir': //Gravar a tarefa no arquivo csv
        if (isset($_POST['tarefa']) && !isset($_POST['id'])) {
            $dados = $_POST['tarefa'] . "\n";
            $fp = fopen($arquivo_txt, 'a');
            if ($fp) {
                // Escrever os dados no arquivo
                if (!fwrite($fp, $dados)) {
                    echo "<h2 class='text-danger text-center'>Erro ao gravar os dados!</h2>";
                }
                // Fechar o arquivo
                fclose($fp);
                header("Location: index.php");
            } else {
                echo "<h2 class='text-danger text-center'>Erro ao abrir o arquivo!</h2>";
            }
        }
        break;
    case 'excluir':  //remover a tarefa do arquivo csv
        if ($linha > 0) {
            $fp = fopen($arquivo_txt, 'r+');
            if ($fp) {
                $linhaAtual = 1;
                $novoConteudo = '';
                // Ler o arquivo linha por linha
                while (($row = fgets($fp)) !== false) {
                    if ($linhaAtual != $linha) {
                        $novoConteudo .= $row;
                    }
                    $linhaAtual++;
                }

                // Verificar se a linha especificada está fora do intervalo
                if ($linha >= $linhaAtual) {
                    fclose($fp);
                    echo "<h2 class='text-danger text-center'>O registro escolhido para exclusão não existe</h2>";
                }

                // Mover o ponteiro de arquivo para o início e truncar o arquivo
                fseek($fp, 0);
                ftruncate($fp, 0);

                // Escrever o novo conteúdo de volta ao arquivo
                fwrite($fp, $novoConteudo);
                fclose($fp);
                header("Location: index.php");
            } else {
                echo "<h2 class='text-danger text-center'>Erro ao abrir o arquivo!</h2>";
            }
        }
        break;
    case 'editartarefa':  //gravar no arquivo a modificação do arquivo csv
        if (isset($_POST['id'])) {

            $id = intval($_POST['id'] - 1);

            // Ler todo o conteúdo do arquivo em memória
            $conteudo = file($arquivo_txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($id > count($conteudo)) {
                echo "<h2 class='text-danger text-center'>O registro escolhido para edição não foi localizado</h2>";
            }

            // Dividir a linha específica em colunas, atualizar os dados e recompor a linha
            $colunas = str_getcsv($conteudo[$id]);
            $colunas[0] = $_POST['tarefa'];
            $conteudo[$id] = implode(',', $colunas);

            // Reescrever o arquivo com o conteúdo atualizado
            if (file_put_contents($arquivo_txt, implode(PHP_EOL, $conteudo) . PHP_EOL)) {
                //Redirecionar para o index.php
                header("Location: index.php");
            }
        }
        break;
    default:
        # code...
        break;
}

?>

<body>
    <div class="container">
        <div class="row mt-4 mb-4">
            <h1>Lista de tarefas</h1>
        </div>
        <div class="row table-responsive table-responsive-sm table-responsive-md ">
            <table class="table table-light border border-4 rounded-3 overflow-hidden bg-light my-auto mx-auto w-auto">
                <tr>
                    <td colspan="3">
                        <form action="index.php?a=inserir" method="post">
                            <div class="d-flex justify-content-center mb-4">
                                <div class="form-outline me-3 w-75">
                                    <input type="text" id="tarefa" name="tarefa" class="form-control" required <?php echo $acao == "listar" ? "autofocus" : ""; ?> placeholder="Digite a tarefa" />
                                </div>
                                <button type="submit" data-mdb-button-init data-mdb-ripple-init
                                    class="btn btn-success">Adicionar</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php

                // Verifica se o arquivo existe
                if (($handle = fopen($arquivo_txt, "r")) !== FALSE) {

                    // Loop através das linhas do arquivo CSV
                    $id = 1;
                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                        echo "<tr>";
                        foreach ($data as $value) {
                            //se a ação for de editar, então mostrar o campo para o usuário editar o registro
                            if ($acao == 'editar' && $id == $linha) {
                                echo "<td colspan='3'>";
                                echo "   <form action='index.php?a=editartarefa' method='post'>";
                                echo "      <div class=\"d-flex justify-content-center\">";
                                echo "         <div data-mdb-input-init class=\"form-outline me-3\" style=\"width: 80%\">";
                                echo "            <input type='hidden' value='" . htmlspecialchars($linha) . "' name='id'>";
                                echo "            <input type='text' value='" . htmlspecialchars($value) . "' name='tarefa' class='form-control' required>";
                                echo "         </div>";
                                echo "         <button type='submit' data-mdb-button-init data-mdb-ripple-init class='btn btn-primary me-1'>Ok</button>";
                                echo "         <a href=\"index.php\" data-mdb-button-init data-mdb-ripple-init class='btn btn-secondary'>Cancelar</a>";
                                echo "      </div>";
                                echo "   </form>";
                                echo "</td>\n";
                            } else {
                                echo "<td>" . htmlspecialchars($value) . "</td>";
                                echo "<td class=\"text-center\"><a href=\"index.php?a=editar&l=$id\" class=\"text-dark\"><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></td>\n";
                                echo "<td class=\"text-center\"><a href=\"index.php?a=excluir&l=$id\" class=\"text-danger\"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></td>\n";
                            }
                        }
                        $id++;
                        echo "</tr>";
                    }
                    fclose($handle);
                } else {
                    echo "Não foi possível abrir o arquivo CSV.";
                }
                ?>
            </table>
        </div>
    </div>
    <footer class="footer fixed-bottom bg-success">
        <div class="container text-center lh-1 mb-2">
            <span class="small lh-1 text-white">
                IFSUL - Campus Santana do Livramento<br>
                Curso Técnico de Informática para Internet Integrado<br>
                Disciplina: Linguagem para internet I<br>
                Prof. Rafael Amorim<br>
            </span>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"
        integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>