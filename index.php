<?php
require 'conexao.php';

function obterPerguntas() {
    global $conn;
    $query = "SELECT id, texto FROM perguntas";
    try {
        $stmt = $conn->query($query);
        $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $perguntas;
    } catch (PDOException $e) {
        echo "Erro na consulta: " . $e->getMessage();
        return [];
    }
}

$perguntas = obterPerguntas();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Avaliação de Prestação de Serviços HRAV</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let perguntas = <?php echo json_encode($perguntas); ?>;
        let indiceAtual = 0;
        let notaSelecionada = null;
        let respostas = [];

        function mostrarAvaliacao() {
            document.getElementById('welcome').style.display = 'none';
            document.getElementById('avaliacao').style.display = 'block';
            mostrarPergunta();
        }

        function mostrarPergunta() {
            const perguntaEl = document.getElementById('pergunta');
            const btnRetroceder = document.getElementById('btnRetroceder');
            const btnAvancar = document.getElementById('btnAvancar');
            const btnEnviar = document.getElementById('btnEnviar');
            const feedbackEl = document.getElementById('feedback');

            if (indiceAtual < perguntas.length) {
                perguntaEl.innerText = perguntas[indiceAtual].texto;
                btnRetroceder.style.display = indiceAtual === 0 ? 'none' : 'inline-block';
                btnAvancar.style.display = indiceAtual === perguntas.length - 1 ? 'none' : 'inline-block';
                btnEnviar.style.display = indiceAtual === perguntas.length - 1 ? 'inline-block' : 'none';
            }

            limparSelecao();
            feedbackEl.value = '';
        }

        function validarEscolha() {
            if (notaSelecionada !== null) {
                return true;
            }
            alert('Por favor, escolha uma nota antes de prosseguir.');
            return false;
        }

        function avancarPergunta() {
            const feedbackEl = document.getElementById('feedback');
            if (validarEscolha()) {
                respostas[indiceAtual] = {
                    id_pergunta: perguntas[indiceAtual].id,
                    resposta: notaSelecionada,
                    feedback: feedbackEl.value || null,
                };
                if (indiceAtual < perguntas.length - 1) {
                    indiceAtual++;
                    mostrarPergunta();
                }
            }
        }

        function retrocederPergunta() {
            if (indiceAtual > 0) {
                indiceAtual--;
                mostrarPergunta();
            }
        }

        function finalizarAvaliacao() {
            if (validarEscolha()) {
                const feedbackEl = document.getElementById('feedback');
                respostas[indiceAtual] = {
                    id_pergunta: perguntas[indiceAtual].id,
                    resposta: notaSelecionada,
                    feedback: feedbackEl.value || null
                };

                fetch('salvar_respostas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(respostas)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Avaliação enviada com sucesso!');
                            window.location.href = 'obrigado.php';
                        } else {
                            alert('Erro ao enviar avaliação. Tente novamente.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro de conexão. Tente novamente mais tarde.');
                    });
            }
        }

        function selecionarNota(nota) {
            notaSelecionada = nota;
            const botoes = document.querySelectorAll('.unidade');
            botoes.forEach((botao) => {
                botao.classList.remove('selecionado');
            });
            const botaoSelecionado = document.querySelector(`button[value='${nota}']`);
            botaoSelecionado.classList.add('selecionado');
        }

        function limparSelecao() {
            const botoes = document.querySelectorAll('.unidade');
            botoes.forEach((botao) => {
                botao.classList.remove('selecionado');
            });
        }
    </script>
</head>

<body>
    <section id="welcome">
        <h1 class="titulo">Bem-vindo ao Sistema de Avaliação do HRAV</h1>
        <p class="subtitulo">Clique no botão para iniciar sua avaliação.</p>
        <button class="btn" id="btnIniciar" onclick="mostrarAvaliacao()">Iniciar Avaliação</button>
    </section>

    <section id="avaliacao" style="display:none;">
        <form id="formAvaliacoes" action="POST">
            <?php if (empty($perguntas)): ?>
                <p class="titulo">Nenhuma pergunta disponível para avaliação.</p>
                <button class="btn" type="button" onclick="alert('Não há perguntas disponíveis!')">Voltar</button>
            <?php else: ?>
                <p class="titulo" id="pergunta"></p>
                <div class="botoes_avaliacao">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <button class="unidade" type="button" value="<?php echo $i; ?>" onclick="selecionarNota(<?php echo $i; ?>)"><?php echo $i; ?></button>
                    <?php endfor; ?>
                </div>
                <div class="btn_container">
                    <button class="btn" type="button" id="btnRetroceder" onclick="retrocederPergunta()" style="display:none;">Retroceder</button>
                    <button class="btn" type="button" id="btnAvancar" onclick="avancarPergunta()">Avançar</button>
                    <button class="btn" type="button" id="btnEnviar" style="display:none;" onclick="finalizarAvaliacao()">Enviar Avaliação</button>
                </div>
                <p class="comentario">Comentários adicionais (opcional):</p>
                <textarea id="feedback" rows="4" cols="50"></textarea>
            <?php endif; ?>
        </form>
        <footer>
            <p class="mensagem_footer">Sua avaliação espontânea é anônima, nenhuma informação pessoal é solicitada ou armazenada.</p>
        </footer>
    </section>
</body>

</html>
