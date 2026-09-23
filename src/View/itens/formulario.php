<?php
/**
 * Formulário usado tanto para CRIAR quanto para EDITAR.
 *
 * @var \App\Core\View $this
 * @var ?\App\Model\Entity\Item $item
 * @var \App\Model\Entity\Categoria[] $categorias
 * @var array $tipos
 * @var array $antigos  dados digitados antes de um erro de validação
 * @var string $acao
 */

// Prioridade: o que o usuário digitou (após erro) > dados do item (edição) > vazio
$valor = [
    'nome'         => $antigos['nome']         ?? $item?->getNome()        ?? '',
    'descricao'    => $antigos['descricao']    ?? $item?->getDescricao()   ?? '',
    'categoria_id' => (int) ($antigos['categoria_id'] ?? $item?->getCategoriaId() ?? 0),
    'tipo'         => $antigos['tipo']         ?? $item?->getTipo()        ?? '',
];
?>
<a href="<?= $this->url($item ? '/itens/' . $item->getId() : '/painel') ?>" class="voltar">← Voltar</a>

<section class="formulario-caixa">
    <h1><?= $this->e($titulo) ?></h1>

    <form action="<?= $this->url($acao) ?>" method="post" enctype="multipart/form-data" class="formulario">
        <?= $this->csrf() ?>

        <label>
            Nome do item *
            <input type="text" name="nome" value="<?= $this->e($valor['nome']) ?>" maxlength="120" required>
        </label>

        <label>
            Descrição
            <textarea name="descricao" rows="5" maxlength="2000" placeholder="Estado de conservação, detalhes, o que aceita em troca..."><?= $this->e($valor['descricao']) ?></textarea>
        </label>

        <div class="formulario__linha">
            <label>
                Categoria *
                <select name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $this->e($categoria->getId()) ?>" <?= $categoria->getId() === $valor['categoria_id'] ? 'selected' : '' ?>>
                            <?= $this->e($categoria->getNome()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <fieldset class="opcoes">
                <legend>Tipo *</legend>
                <?php foreach ($tipos as $chave => $rotulo): ?>
                    <label class="opcao">
                        <input type="radio" name="tipo" value="<?= $this->e($chave) ?>" <?= $valor['tipo'] === $chave ? 'checked' : '' ?> required>
                        <?= $this->e($rotulo) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        </div>

        <label>
            Foto (opcional — JPG, PNG ou WEBP até 2 MB)
            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-preview-foto>
        </label>

        <div class="preview" data-preview>
            <?php if ($item?->getFoto()): ?>
                <img src="<?= $this->url('/uploads/' . $item->getFoto()) ?>" alt="Foto atual">
            <?php endif; ?>
        </div>

        <?php if ($item?->getFoto()): ?>
            <label class="opcao">
                <input type="checkbox" name="remover_foto" value="1"> Remover a foto atual
            </label>
        <?php endif; ?>

        <div class="formulario__acoes">
            <button type="submit" class="botao"><?= $item ? 'Salvar alterações' : 'Publicar item' ?></button>
        </div>
    </form>
</section>
