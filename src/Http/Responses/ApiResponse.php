<?php

namespace Example\CrmContactModule\Http\Responses;

use Example\CrmContactModule\Contracts\ApiResponseInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements ApiResponseInterface, Arrayable
{
    private mixed $data;
    private array $transformRules;

    public function __construct(array $transformRules = [])
    {
        $this->transformRules = $transformRules;
    }

    public function fromData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function toResponse($request): Response
    {
        $format = $request->getAcceptableContentTypes()[0] ?? 'application/json';
        
        return match ($format) {
            'application/xml' => response()->xml($this->toArray()),
            'application/json' => response()->json($this->toArray()),
            default => response()->json($this->toArray()),
        };
    }

    public function toArray(): array
    {
        if ($this->data instanceof LengthAwarePaginator) {
            return [
                'data' => $this->data->map(fn($item) => $this->transformItem($item))->toArray(),
                'meta' => [
                    'current_page' => $this->data->currentPage(),
                    'last_page' => $this->data->lastPage(),
                    'per_page' => $this->data->perPage(),
                    'total' => $this->data->total(),
                ],
                'links' => [
                    'first' => $this->data->url(1),
                    'last' => $this->data->url($this->data->lastPage()),
                    'prev' => $this->data->previousPageUrl(),
                    'next' => $this->data->nextPageUrl(),
                ],
            ];
        }

        if ($this->data instanceof Collection) {
            return [
                'data' => $this->data->map(fn($item) => $this->transformItem($item))->toArray(),
                'meta' => [
                    'total' => $this->data->count(),
                ],
            ];
        }

        return $this->transformItem($this->data);
    }

    private function transformItem($item): array
    {
        if (!isset($this->transformRules[$item::class])) {
            return $item instanceof Arrayable ? $item->toArray() : (array) $item;
        }

        $transformed = ($this->transformRules[$item::class])($item);

        // Add HATEOAS links if the entity has an ID
        if (method_exists($item, 'getId')) {
            $transformed['_links'] = $this->generateLinks($item);
        }

        return $transformed;
    }

    private function generateLinks($item): array
    {
        $links = ['self' => ['href' => $this->generateSelfLink($item)]];

        // Add related resource links
        if ($item instanceof Contact) {
            $links['calls'] = ['href' => "/api/contacts/{$item->getId()}/calls"];
        } elseif ($item instanceof ContactCall) {
            $links['contact'] = ['href' => "/api/contacts/{$item->getContact()->getId()}"];
        }

        return $links;
    }

    private function generateSelfLink($item): string
    {
        $type = match (true) {
            $item instanceof Contact => 'contacts',
            $item instanceof ContactCall => 'calls',
            default => strtolower(class_basename($item)) . 's',
        };

        return "/api/{$type}/{$item->getId()}";
    }
} 