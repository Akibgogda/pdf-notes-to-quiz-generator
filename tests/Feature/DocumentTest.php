<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_documents_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('documents.index'));

        $response->assertStatus(200);
        $response->assertSee('Stored Documents');
    }

    public function test_user_can_upload_and_store_text_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $content = str_repeat('Photosynthesis is the process by which green plants make food using sunlight. ', 3);
        $file = UploadedFile::fake()->createWithContent('biology_notes.txt', $content);

        $response = $this->actingAs($user)->post(route('documents.store'), [
            'title' => 'Biology Lesson 1',
            'file' => $file,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'title' => 'Biology Lesson 1',
            'original_name' => 'biology_notes.txt',
        ]);
    }

    public function test_user_can_download_their_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $filePath = 'documents/test.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 Fake PDF Content');

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'Test Document',
            'original_name' => 'test.pdf',
            'file_path' => $filePath,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'extracted_text' => 'Sample extracted text content',
        ]);

        $response = $this->actingAs($user)->get(route('documents.download', $document));

        $response->assertStatus(200);
    }

    public function test_user_cannot_download_another_users_document(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::create([
            'user_id' => $owner->id,
            'title' => 'Private Document',
            'original_name' => 'secret.pdf',
            'file_path' => 'documents/secret.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'extracted_text' => 'Secret text',
        ]);

        $response = $this->actingAs($otherUser)->get(route('documents.download', $document));

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $filePath = 'documents/delete_me.pdf';
        Storage::disk('local')->put($filePath, 'content');

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'To Delete',
            'original_name' => 'delete_me.pdf',
            'file_path' => $filePath,
            'file_size' => 100,
            'mime_type' => 'application/pdf',
            'extracted_text' => 'Sample text',
        ]);

        $response = $this->actingAs($user)->delete(route('documents.destroy', $document));

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
        Storage::disk('local')->assertMissing($filePath);
    }
}
