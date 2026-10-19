<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Note;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $employee1;
    protected User $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'مالك المتجر',
            'username' => 'owner_notes',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->employee1 = User::create([
            'name' => 'شهد أحمد',
            'username' => 'shahd_notes',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->employee2 = User::create([
            'name' => 'علي حسن',
            'username' => 'ali_notes',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'is_active' => true,
        ]);
    }

    public function test_employee_can_view_notes_index_and_create_page(): void
    {
        $response = $this->actingAs($this->employee1)->get(route('notes.index'));
        $response->assertOk();
        $response->assertViewIs('notes.index');

        $createResponse = $this->actingAs($this->employee1)->get(route('notes.create'));
        $createResponse->assertOk();
        $createResponse->assertViewIs('notes.create');
        $createResponse->assertSee('فلوس متأجلة');
        $createResponse->assertSee('طلب أو أوردر معلق');
    }

    public function test_employee_can_create_note_with_predefined_title(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'فلوس متأجلة',
            'content' => 'العميل محمد عليه متبقي 250 جنيه مصري من هدية التخرج',
            'amount' => 250.00,
            'status' => 'open',
        ]);

        $response->assertRedirect(route('notes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notes', [
            'user_id' => $this->employee1->id,
            'title' => 'فلوس متأجلة',
            'amount' => 250.00,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'note_created',
            'user_id' => $this->employee1->id,
        ]);
    }

    public function test_employee_can_create_note_with_custom_title_when_choosing_other(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'أخرى',
            'custom_title' => 'تغليف بوكس هدايا مميز VIP',
            'content' => 'مطلوب تجهيز وتغليف خاص قبل الساعة 6 مساءً',
            'amount' => 500.00,
        ]);

        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'user_id' => $this->employee1->id,
            'title' => 'تغليف بوكس هدايا مميز VIP',
            'amount' => 500.00,
            'status' => 'open',
        ]);
    }

    public function test_server_automatically_generates_creation_timestamp_with_date_and_time(): void
    {
        $before = now()->subSecond();

        $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'ملاحظات الوردية',
            'content' => 'تم استلام الخزينة وجرد العهدة بكل دقة.',
        ]);

        $after = now()->addSecond();

        $note = Note::where('user_id', $this->employee1->id)->latest('id')->first();
        $this->assertNotNull($note);
        $this->assertNotNull($note->created_at);
        $this->assertTrue($note->created_at->between($before, $after));
        $this->assertNotEmpty($note->created_at->format('Y-m-d H:i:s'));
    }

    public function test_optional_amount_and_due_date_are_stored_correctly(): void
    {
        $dueDate = '2026-10-25 15:30:00';

        $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'متابعة عميل',
            'content' => 'الاتصال بالعميل لتأكيد موعد استلام باقة الورد',
            'amount' => 175.50,
            'due_at' => $dueDate,
        ]);

        $note = Note::where('user_id', $this->employee1->id)->first();
        $this->assertNotNull($note);
        $this->assertEquals(175.50, (float) $note->amount);
        $this->assertEquals('2026-10-25 15:30:00', $note->due_at->format('Y-m-d H:i:s'));
    }

    public function test_cannot_spoof_creator_user_id_through_request_input(): void
    {
        // Employee 1 attempts to create note under employee 2 or owner id
        $this->actingAs($this->employee1)->post(route('notes.store'), [
            'user_id' => $this->employee2->id,
            'title_type' => 'مشكلة أو تنبيه',
            'content' => 'عطل مؤقت في طابعة الإيصالات الحرارية',
        ]);

        $note = Note::latest('id')->first();
        $this->assertNotNull($note);
        // Must strictly belong to the authenticated user (employee 1), not employee 2
        $this->assertEquals($this->employee1->id, $note->user_id);
        $this->assertNotEquals($this->employee2->id, $note->user_id);
    }

    public function test_employee_can_view_and_edit_their_own_note(): void
    {
        $note = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'نواقص أو طلب بضاعة',
            'content' => 'نقص في علب الهدايا الكبيرة الحجم',
            'amount' => null,
            'status' => 'open',
        ]);

        // Show
        $showResponse = $this->actingAs($this->employee1)->get(route('notes.show', $note));
        $showResponse->assertOk();
        $showResponse->assertSee('نقص في علب الهدايا الكبيرة الحجم');

        // Edit view
        $editResponse = $this->actingAs($this->employee1)->get(route('notes.edit', $note));
        $editResponse->assertOk();

        // Update
        $updateResponse = $this->actingAs($this->employee1)->patch(route('notes.update', $note), [
            'title_type' => 'نواقص أو طلب بضاعة',
            'content' => 'تم طلب علب الهدايا الكبيرة وننتظر وصول الشحنة غداً',
            'amount' => 300.00,
            'status' => 'open',
        ]);

        $updateResponse->assertRedirect(route('notes.index'));
        $note->refresh();
        $this->assertEquals('تم طلب علب الهدايا الكبيرة وننتظر وصول الشحنة غداً', $note->content);
        $this->assertEquals(300.00, (float) $note->amount);
    }

    public function test_employee_can_mark_note_as_completed(): void
    {
        $note = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'طلب أو أوردر معلق',
            'content' => 'حجز دبدوب كبير وشوكولاتة للعميلة سارة',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->employee1)->patch(route('notes.complete', $note));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $note->refresh();
        $this->assertTrue($note->isCompleted());
        $this->assertEquals('completed', $note->status);
    }

    public function test_employee_can_cancel_note(): void
    {
        $note = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'طلب أو أوردر معلق',
            'content' => 'العميل ألغى الحجز الخاص به',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->employee1)->patch(route('notes.cancel', $note));
        $response->assertRedirect();

        $note->refresh();
        $this->assertTrue($note->isCancelled());
        $this->assertEquals('cancelled', $note->status);
    }

    public function test_employee_can_soft_delete_or_archive_note(): void
    {
        $note = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'ملاحظات الوردية',
            'content' => 'ملاحظة قديمة للأرشفة',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->employee1)->delete(route('notes.destroy', $note));
        $response->assertRedirect(route('notes.index'));

        // Preserved in database via soft deletes (not permanently wiped)
        $this->assertSoftDeleted('notes', ['id' => $note->id]);
    }

    public function test_employee_cannot_see_other_employees_notes_in_index(): void
    {
        $noteEmp1 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'ملاحظة شهد الخاصة',
            'content' => 'محتوى سري لشهد',
            'status' => 'open',
        ]);

        $noteEmp2 = Note::create([
            'user_id' => $this->employee2->id,
            'title' => 'ملاحظة علي الخاصة',
            'content' => 'محتوى سري لعلي',
            'status' => 'open',
        ]);

        // Employee 1 index
        $resEmp1 = $this->actingAs($this->employee1)->get(route('notes.index'));
        $resEmp1->assertOk();
        $resEmp1->assertSee('ملاحظة شهد الخاصة');
        $resEmp1->assertDontSee('ملاحظة علي الخاصة');

        // Even if Employee 1 passes ?user_id=employee2->id, backend query denies cross-user visibility
        $sneakyRes = $this->actingAs($this->employee1)->get(route('notes.index', ['user_id' => $this->employee2->id]));
        $sneakyRes->assertOk();
        $sneakyRes->assertSee('ملاحظة شهد الخاصة');
        $sneakyRes->assertDontSee('ملاحظة علي الخاصة');
    }

    public function test_employee_cannot_view_or_edit_or_delete_other_employees_note(): void
    {
        $noteEmp2 = Note::create([
            'user_id' => $this->employee2->id,
            'title' => 'ملاحظة علي الخاصة',
            'content' => 'محتوى لعلي فقط',
            'status' => 'open',
        ]);

        // Employee 1 tries to view
        $this->actingAs($this->employee1)->get(route('notes.show', $noteEmp2))->assertForbidden();

        // Employee 1 tries to edit
        $this->actingAs($this->employee1)->get(route('notes.edit', $noteEmp2))->assertForbidden();

        // Employee 1 tries to update
        $this->actingAs($this->employee1)->patch(route('notes.update', $noteEmp2), [
            'title_type' => 'مشكلة أو تنبيه',
            'content' => 'محاولة تعديل غير مصرحة',
        ])->assertForbidden();

        // Employee 1 tries to complete
        $this->actingAs($this->employee1)->patch(route('notes.complete', $noteEmp2))->assertForbidden();

        // Employee 1 tries to cancel
        $this->actingAs($this->employee1)->patch(route('notes.cancel', $noteEmp2))->assertForbidden();

        // Employee 1 tries to delete
        $this->actingAs($this->employee1)->delete(route('notes.destroy', $noteEmp2))->assertForbidden();
    }

    public function test_owner_can_see_all_notes_and_filter_by_employee(): void
    {
        $noteEmp1 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'ملاحظة شهد',
            'content' => 'محتوى شهد',
            'status' => 'open',
        ]);

        $noteEmp2 = Note::create([
            'user_id' => $this->employee2->id,
            'title' => 'ملاحظة علي',
            'content' => 'محتوى علي',
            'status' => 'completed',
        ]);

        // Owner sees all notes
        $allRes = $this->actingAs($this->owner)->get(route('notes.index'));
        $allRes->assertOk();
        $allRes->assertSee('ملاحظة شهد');
        $allRes->assertSee('ملاحظة علي');

        // Owner filters by employee 1
        $emp1Res = $this->actingAs($this->owner)->get(route('notes.index', ['user_id' => $this->employee1->id]));
        $emp1Res->assertOk();
        $emp1Res->assertSee('ملاحظة شهد');
        $emp1Res->assertDontSee('ملاحظة علي');
    }

    public function test_owner_can_view_and_manage_any_note(): void
    {
        $noteEmp1 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'ملاحظة موظف',
            'content' => 'محتوى موظف',
            'status' => 'open',
        ]);

        $this->actingAs($this->owner)->get(route('notes.show', $noteEmp1))->assertOk();
        $this->actingAs($this->owner)->get(route('notes.edit', $noteEmp1))->assertOk();

        $this->actingAs($this->owner)->patch(route('notes.complete', $noteEmp1))->assertRedirect();
        $this->assertEquals('completed', $noteEmp1->fresh()->status);
    }

    public function test_search_and_filters_work_as_expected(): void
    {
        $note1 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'فلوس متأجلة',
            'content' => 'دين متبقي بقيمة 1500 على شركة النور',
            'amount' => 1500.00,
            'due_at' => now()->subDay(), // overdue
            'status' => 'open',
        ]);

        $note2 = Note::create([
            'user_id' => $this->employee1->id,
            'title' => 'متابعة عميل',
            'content' => 'متابعة عميل لتجهيز بوكيه ورد',
            'amount' => 200.00,
            'due_at' => now()->addDays(5),
            'status' => 'completed',
        ]);

        // Filter by status open
        $resStatus = $this->actingAs($this->employee1)->get(route('notes.index', ['status' => 'open']));
        $resStatus->assertSee('شركة النور');
        $resStatus->assertDontSee('بوكيه ورد');

        // Filter by title
        $resTitle = $this->actingAs($this->employee1)->get(route('notes.index', ['title' => 'متابعة عميل']));
        $resTitle->assertSee('بوكيه ورد');
        $resTitle->assertDontSee('شركة النور');

        // Filter by overdue
        $resOverdue = $this->actingAs($this->employee1)->get(route('notes.index', ['due_status' => 'overdue']));
        $resOverdue->assertSee('شركة النور');
        $resOverdue->assertDontSee('بوكيه ورد');

        // Search text
        $resSearch = $this->actingAs($this->employee1)->get(route('notes.index', ['search' => 'شركة النور']));
        $resSearch->assertSee('شركة النور');
        $resSearch->assertDontSee('بوكيه ورد');
    }

    public function test_validation_fails_when_content_is_missing(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'فلوس متأجلة',
            'content' => '',
        ]);

        $response->assertSessionHasErrors('content');
    }

    public function test_validation_fails_when_custom_title_is_missing_for_other_type(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'أخرى',
            'custom_title' => '',
            'content' => 'تفاصيل ملاحظة بدون عنوان مخصص',
        ]);

        $response->assertSessionHasErrors(['custom_title', 'title_type']);
    }

    public function test_validation_fails_when_amount_is_negative(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'فلوس متأجلة',
            'content' => 'ملاحظة بمبلغ غير صحيح',
            'amount' => -50,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_validation_fails_for_invalid_status_or_title_type(): void
    {
        $response = $this->actingAs($this->employee1)->post(route('notes.store'), [
            'title_type' => 'عنوان غير موجود',
            'content' => 'محتوى صحيح',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['title_type', 'status']);
    }
}

