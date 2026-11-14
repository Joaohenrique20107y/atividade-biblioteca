<?php
declare(strict_types=1);

final class Leitor
{
    public function __construct(
        public ?int $id,
        public string $nome,
        public string $email
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['id']) ? (int) $data['id'] : null,
            trim((string) ($data['nome'] ?? '')),
            trim((string) ($data['email'] ?? ''))
        );
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'nome' => $this->nome, 'email' => $this->email];
    }
}
