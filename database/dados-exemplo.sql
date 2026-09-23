-- =====================================================================
-- (Opcional) Dados de exemplo para demonstração.
-- Rode DEPOIS do schema.sql. Os dois usuários têm a senha: bazar123
-- =====================================================================

USE bazar_universitario;

INSERT INTO usuarios (nome, email, senha_hash) VALUES
    ('Ana Souza',   'ana@exemplo.com',   '$2y$12$VQp6iJomHTTH8LOKxHf7cu39tzozODwZZhEXH6qDtQ1qLabnvIrXq'),
    ('Bruno Lima',  'bruno@exemplo.com', '$2y$12$VQp6iJomHTTH8LOKxHf7cu39tzozODwZZhEXH6qDtQ1qLabnvIrXq');

INSERT INTO itens (usuario_id, categoria_id, nome, descricao, tipo) VALUES
    (1, 1, 'Cálculo Vol. 1 — James Stewart', 'Livro em bom estado, algumas anotações a lápis nos capítulos 2 e 3.', 'doacao'),
    (1, 3, 'Calculadora científica Casio fx-82', 'Funcionando perfeitamente, acompanha capa.', 'troca'),
    (2, 2, 'Mouse sem fio Logitech', 'Pouco uso. Troco por um teclado ou fone de ouvido.', 'troca'),
    (2, 3, 'Kit de canetas e marca-texto', 'Sobrou do semestre passado, tudo novo.', 'doacao'),
    (2, 1, 'Use a Cabeça! Java', 'Ótimo para quem está começando em POO.', 'doacao');
