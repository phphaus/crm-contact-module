<?php

namespace Example\CrmContactModule\Http\Controllers\Api;

use Example\CrmContactModule\Contracts\ContactServiceInterface;
use Example\CrmContactModule\Http\Requests\Contact\StoreContactRequest;
use Example\CrmContactModule\Http\Requests\Contact\UpdateContactRequest;
use Example\CrmContactModule\Exceptions\ContactNotFoundException;
use Example\CrmContactModule\Exceptions\ValidationException;
use Example\CrmContactModule\Exceptions\CallFailedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Example\CrmContactModule\Http\Requests\ListContactsRequest;

#[OA\Info(version: "1.0.0", title: "CRM Example API")]
class ContactController extends Controller
{
    public function __construct(
        private readonly ContactServiceInterface $contactService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/contacts",
     *     summary="List contacts",
     *     tags={"Contacts"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Filter by phone number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter by email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of contacts",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Contact")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(ListContactsRequest $request): JsonResponse
    {
        $contacts = $this->contactService->listContacts(
            phone: $request->input('phone'),
            email: $request->input('email'),
            perPage: $request->getPerPage(),
            page: $request->getPage()
        );

        return response()->json($contacts);
    }

    #[OA\Get(
        path: "/api/v1/contacts/{id}",
        summary: "Get a contact by ID",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Contact details"),
            new OA\Response(response: 404, description: "Contact not found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->getContact($id);
            return response()->json($contact);
        } catch (ContactNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[OA\Post(
        path: "/api/v1/contacts",
        summary: "Create a new contact",
        requestBody: new OA\RequestBody(required: true),
        tags: ["Contacts"],
        responses: [
            new OA\Response(response: 201, description: "Contact created"),
            new OA\Response(response: 400, description: "Validation error")
        ]
    )]
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->contactService->createContact($request->validated());
            return response()->json($contact, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Put(
        path: "/api/v1/contacts/{id}",
        summary: "Update a contact",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Contact updated"),
            new OA\Response(response: 404, description: "Contact not found"),
            new OA\Response(response: 400, description: "Validation error")
        ]
    )]
    public function update(UpdateContactRequest $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->updateContact($id, $request->validated());
            return response()->json($contact);
        } catch (ContactNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[OA\Delete(
        path: "/api/v1/contacts/{id}",
        summary: "Delete a contact",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Contact deleted"),
            new OA\Response(response: 404, description: "Contact not found")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->contactService->deleteContact($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ContactNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[OA\Post(
        path: "/api/v1/contacts/{id}/call",
        summary: "Record a call for a contact",
        tags: ["Contacts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Call recorded"),
            new OA\Response(response: 404, description: "Contact not found"),
            new OA\Response(response: 400, description: "Invalid status")
        ]
    )]
    public function recordCall(Request $request, int $id): JsonResponse
    {
        try {
            // Start the call process
            $this->contactService->recordCall($id, 'initiated');
            
            return response()->json([
                'message' => 'Call initiated successfully',
                'status' => 'initiated'
            ]);

        } catch (ContactNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (CallFailedException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
} 