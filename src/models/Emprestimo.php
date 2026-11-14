<?php
declare(strict_types=1);

final class Emprestimo
{
    public function __construct(
        public ?int $id,
        public int $livro_id,
        public int $leitor_id,
        public ?string $data_emprestimo = null,
        public ?string $data_devolucao = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            (int) ($data['livro_id'] ?? 0),
            (int) ($data['leitor_id'] ?? 0),
            isset($data['data_emprestimo']) ? (string) $data['data_emprestimo'] : null,
            isset($data['data_devolucao']) ? (string) $data['data_devolucao'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'livro_id' => $this->livro_id,
            'leitor_id' => $this->leitor_id,
            'data_emprestimo' => $this->data_emprestimo,
            'data_devolucao' => $this->data_devolucao,
        ];
    }
}
