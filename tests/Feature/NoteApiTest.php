<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_can_create_note()
    {
        $headers = $this->authenticate();
        $data = ['title' => 'Test Note', 'content' => 'Test content'];
        $this->postJson('/api/notes', $data, $headers)
             ->assertStatus(201)
             ->assertJson(['title' => 'Test Note']);
    }

    public function test_can_get_all_notes()
    {
        $headers = $this->authenticate();

        // Clear the table to ensure there are no leftover records
        Note::truncate();

        Note::factory()->count(3)->create();

        $this->getJson('/api/notes', $headers)
             ->assertStatus(200)
             ->assertJsonCount(3);
    }

    public function test_can_get_single_note()
    {
        $headers = $this->authenticate();
        $note = Note::factory()->create();
        $this->getJson('/api/notes/' . $note->id, $headers)
             ->assertStatus(200)
             ->assertJson(['id' => $note->id]);
    }

    public function test_can_update_note()
    {
        $headers = $this->authenticate();
        $note = Note::factory()->create();
        $data = ['title' => 'Updated Note', 'content' => 'Updated content'];
        $this->putJson('/api/notes/' . $note->id, $data, $headers)
             ->assertStatus(200)
             ->assertJson(['title' => 'Updated Note']);
    }

    public function test_can_delete_note()
    {
        $headers = $this->authenticate();
        $note = Note::factory()->create();
        $this->deleteJson('/api/notes/' . $note->id, [], $headers)
             ->assertStatus(200);
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_unauthenticated_user_cannot_access_notes()
    {
        $this->getJson('/api/notes')
             ->assertStatus(401);
    }
}
