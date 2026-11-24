let idAtual = null;
let pecaAtual = null;

/* VIBRAÇÃO */

function vibrar() {

  if (navigator.vibrate) {
    navigator.vibrate(200);
  }

}

/* LIMPAR QR */

function limparQR(texto) {

  return (texto || "")
    .toString()
    .replace(/peca:|PEC:/gi, "")
    .trim()
    .toUpperCase();

}

/* EXIBIR PEÇA */

function exibirPeca(peca) {

  if (!peca) return;

  pecaAtual = peca;

  let historicoHTML = "";

  (peca.historico || [])
    .slice()
    .reverse()
    .forEach(item => {

      historicoHTML += `
        <tr>
          <td>${item.data || "-"}</td>
          <td>${item.horario || "-"}</td>
          <td>${item.local || "-"}</td>
        </tr>
      `;

    });

  document.getElementById("resultado").innerHTML = `

    <div class="dados-peca">

      <h2>Dados da Peça</h2>

      <p><strong>Número:</strong> ${peca.id}</p>

      <p><strong>Lote:</strong> ${peca.lote}</p>

      <p><strong>Data de Fabricação:</strong> ${peca.dataFabricacao}</p>

      <p><strong>Local:</strong> ${peca.local}</p>

      <p><strong>Horário:</strong> ${peca.horario}</p>

      <p><strong>Status:</strong> ${peca.status}</p>

    </div>

    <div class="historico">

      <h3>Histórico de Movimentação</h3>

      <table class="tabela-historico">

        <thead>

          <tr>
            <th>Data</th>
            <th>Horário</th>
            <th>Local</th>
          </tr>

        </thead>

        <tbody>

          ${historicoHTML}

        </tbody>

      </table>

    </div>

  `;

}

/* BUSCAR PEÇA */

async function buscarPeca(id) {

  try {

    const idLimpo = limparQR(id);

    const resposta = await fetch(
      `/api/pecas/${encodeURIComponent(idLimpo)}`
    );

    if (!resposta.ok) {
      throw new Error("Peça não encontrada");
    }

    const dados = await resposta.json();

    idAtual = idLimpo;

    vibrar();

    exibirPeca(dados);

  } catch (err) {

    console.error(err);

    alert("Peça não encontrada");

  }

}

/* ATUALIZAR LOCAL */

async function atualizarLocal() {

  if (!idAtual) {

    alert("Leia um QR Code primeiro");

    return;

  }

  const localInput = document.getElementById("local");

  const local = localInput.value.trim();

  if (!local) {

    alert("Selecione o novo local");

    return;

  }

  try {

    const resposta = await fetch(

      `/api/pecas/${encodeURIComponent(idAtual)}`,

      {
        method: "PUT",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify({
          local: local
        })
      }

    );

    const dados = await resposta.json();

    if (!resposta.ok) {

      alert(dados.erro || "Erro ao atualizar");

      return;

    }

    exibirPeca(dados.dados);

    localInput.value = "";

    alert("Atualizado com sucesso!");

  } catch (err) {

    console.error(err);

    alert("Erro ao atualizar localização");

  }

}

/* LOCAIS (listas) */

async function carregarLocais() {

  try {

    const resposta = await fetch("/api/locais", {
      headers: { "Accept": "application/json" }
    });

    if (!resposta.ok) {
      throw new Error("Erro ao carregar locais");
    }

    const locais = await resposta.json();

    ["local", "cadLocal"].forEach(idSelect => {

      const select = document.getElementById(idSelect);

      if (!select) return;

      locais.forEach(nome => {

        const opcao = document.createElement("option");

        opcao.value = nome;
        opcao.textContent = nome;

        select.appendChild(opcao);

      });

    });

  } catch (err) {

    console.error(err);

    alert("Não foi possível carregar a lista de locais");

  }

}

/* CADASTRAR PEÇA */

async function cadastrarPeca(evento) {

  evento.preventDefault();

  const form = evento.target;

  const corpo = {
    id: limparQR(document.getElementById("cadId").value),
    lote: document.getElementById("cadLote").value.trim(),
    data_fabricacao: document.getElementById("cadData").value,
    horario: document.getElementById("cadHorario").value,
    local: document.getElementById("cadLocal").value,
    status: document.getElementById("cadStatus").value.trim()
  };

  try {

    const resposta = await fetch("/api/pecas", {

      method: "POST",

      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },

      body: JSON.stringify(corpo)

    });

    const dados = await resposta.json();

    if (!resposta.ok) {

      alert(dados.erro || "Erro ao cadastrar");

      return;

    }

    form.reset();

    alert("Peça cadastrada com sucesso!");

    buscarPeca(dados.dados.id);

  } catch (err) {

    console.error(err);

    alert("Erro ao cadastrar peça");

  }

}

/* GERAR PDF COM OS DADOS DA TABELA */

function baixarPDF() {

  if (!pecaAtual) {

    alert("Leia um QR Code primeiro");

    return;

  }

  if (!window.jspdf) {

    alert("Biblioteca de PDF não carregada. Verifique a conexão com a internet.");

    return;

  }

  const { jsPDF } = window.jspdf;

  const doc = new jsPDF();

  const peca = pecaAtual;

  doc.setFontSize(16);
  doc.text("Relatório de Rastreabilidade", 14, 18);

  doc.setFontSize(10);
  doc.text("AMSTED MAXION - Sistema de Rastreabilidade Industrial", 14, 25);
  doc.text("Emitido em: " + new Date().toLocaleString("pt-BR"), 14, 31);

  doc.autoTable({

    startY: 38,

    head: [["Dados da Peça", ""]],

    body: [
      ["Número", peca.id],
      ["Lote", peca.lote],
      ["Data de Fabricação", peca.dataFabricacao],
      ["Local", peca.local],
      ["Horário", peca.horario],
      ["Status", peca.status]
    ],

    theme: "grid",

    headStyles: { fillColor: [10, 25, 47] },

    columnStyles: { 0: { fontStyle: "bold", cellWidth: 55 } }

  });

  const historico = (peca.historico || []).map(item => [
    item.data || "-",
    item.horario || "-",
    item.local || "-"
  ]);

  doc.autoTable({

    startY: doc.lastAutoTable.finalY + 10,

    head: [["Data", "Horário", "Local"]],

    body: historico.length ? historico : [["-", "-", "-"]],

    theme: "grid",

    headStyles: { fillColor: [10, 25, 47] }

  });

  doc.save("relatorio_rastreabilidade_" + peca.id + ".pdf");

}

/* QR CODE */

function iniciarLeitor() {

  const leitor = new Html5QrcodeScanner(

    "reader",

    {
      fps: 10,
      qrbox: 250
    },

    false

  );

  leitor.render(

    (textoLido) => {

      const id = limparQR(textoLido);

      buscarPeca(id);

    }

  );

}

carregarLocais();

iniciarLeitor();
