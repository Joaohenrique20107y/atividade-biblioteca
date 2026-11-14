<?php
declare(strict_types=1);

final class Livro
{
    public function __construct(
        public ?int $id,
        public string $titulo,
        public string $autor,
        public int $ano,
        public int $quantidade
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            trim((string) ($data['titulo'] ?? '')),
            trim((string) ($data['autor'] ?? '')),
            (int) ($data['ano'] ?? 0),
            (int) ($data['quantidade'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'autor' => $this->autor,
            'ano' => $this->ano,
            'quantidade' => $this->quantidade,
        ];
    }
}
