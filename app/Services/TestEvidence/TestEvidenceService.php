<?php

namespace App\Services\TestEvidence;

use App\Models\TestEvidence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TestEvidenceService
{
    /**
     * Upload evidence for a bug report
     */
    public function uploadEvidenceForBug($bugId, $stepNumber, $stepDescription, UploadedFile $screenshot, $notes = null, $context = null)
    {
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $screenshot->getClientOriginalExtension();
        // Store the file in the public disk under 'evidence' folder
        $path = $screenshot->storeAs('evidence', $filename, 'public');
        // Create evidence record
        $evidence = TestEvidence::create([
            'bug_id' => $bugId,
            'step_number' => $stepNumber,
            'step_description' => $stepDescription,
            'screenshot_url' => Storage::url($path),
            'notes' => $notes,
            'context' => $context // Add the context parameter
        ]);
        return $evidence;
    }
    /**
     * Upload evidence for a task (not tied to a specific bug)
     */
    public function uploadEvidenceForTask($taskId, $stepNumber, $stepDescription, UploadedFile $screenshot, $notes = null, $context = null)
    {
        // Generate a unique filename
        $filename = Str::uuid() . '.' . $screenshot->getClientOriginalExtension();
        // Store the file in the public disk under 'evidence' folder
        $path = $screenshot->storeAs('evidence', $filename, 'public');
        // Create evidence record
        $evidence = TestEvidence::create([
            'task_id' => $taskId,
            'step_number' => $stepNumber,
            'step_description' => $stepDescription,
            'screenshot_url' => Storage::url($path),
            'notes' => $notes,
            'context' => $context
        ]);
        return $evidence;
    }

    /**
     * Get all evidence for a bug report
     */
    public function getEvidenceForBug($bugId)
    {
        return TestEvidence::where('bug_id', $bugId)
            ->orderBy('step_number')
            ->get();
    }

    /**
     * Get all evidence for a task
     */
    public function getEvidenceForTask($taskId)
    {
        return TestEvidence::where('task_id', $taskId)
            ->orderBy('step_number')
            ->get();
    }

    /**
     * Delete an evidence item
     */
    public function deleteEvidence($evidenceId)
    {
        $evidence = TestEvidence::find($evidenceId);

        if (!$evidence) {
            throw new \Exception('Evidence not found');
        }

        // Extract the filename from the URL
        $url = $evidence->screenshot_url;
        $path = str_replace(Storage::url(''), '', $url);

        // Delete the file
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        // Delete the record
        return $evidence->delete();
    }

    /**
     * Update an evidence item
     */
    public function updateEvidence($evidenceId, array $data)
    {
        $evidence = TestEvidence::find($evidenceId);

        if (!$evidence) {
            throw new \Exception('Evidence not found');
        }

        // Update the evidence record
        $evidence->update($data);

        return $evidence->fresh();
    }
}