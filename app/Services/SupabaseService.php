<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
  protected string $baseUrl;
  protected string $apiKey;
  protected string $schema = 'public';
  protected string $table = '';
  protected array $queryParams = [];

  public function __construct()
  {
    $this->baseUrl = rtrim(env('SUPABASE_REST_URL'), '/');
    $this->apiKey = env('SUPABASE_ANON_KEY');
  }

  public function table(string $table): static
  {
    $this->table = $table;
    $this->queryParams = [];
    return $this;
  }

  public function select(string $columns): static
  {
    $this->queryParams['select'] = $columns;
    return $this;
  }

  public function order(string $column, string $direction = 'asc'): static
  {
    $this->queryParams['order'] = "{$column}.{$direction}";
    return $this;
  }

  public function eq(string $column, string $value): static
  {
    $this->queryParams[$column] = "eq.$value";
    return $this;
  }

  public function limit(int $limit): static
  {
    $this->queryParams['limit'] = $limit;
    return $this;
  }

  protected function buildUrl(): string
  {
    $query = http_build_query($this->queryParams);
    return "{$this->baseUrl}/{$this->table}?{$query}";
  }

  protected function client()
  {
    return Http::withHeaders([
      'apikey'        => $this->apiKey,
      'Authorization' => "Bearer {$this->apiKey}",
      'Content-Type'  => 'application/json'
    ])->withoutVerifying(); // Fix cURL SSL error 60
  }

  public function get()
  {
    return $this->client()->get($this->buildUrl())->json();
  }

  public function insert(array $data)
  {
    return $this->client()
      ->post("{$this->baseUrl}/{$this->table}", $data)
      ->json();
  }

  public function update(string $id, array $data)
  {
    return $this->client()
      ->patch("{$this->baseUrl}/{$this->table}?id=eq.$id", $data)
      ->json();
  }

  public function delete(string $id)
  {
    return $this->client()
      ->delete("{$this->baseUrl}/{$this->table}?id=eq.$id")
      ->json();
  }
}
