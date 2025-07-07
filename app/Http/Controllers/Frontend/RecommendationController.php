<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\Child;
use App\Models\RecommendationAttachment;
use App\Notifications\NewRecommendationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Models\RecommendationResponse;

class RecommendationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Check if filtering by specific child
        $childId = request('child_id');
        
        if ($user->hasRole('Trainer')) {
            // Trainers see their own recommendations
            $query = Recommendation::with(['child', 'attachments', 'responses'])
                ->where('trainer_id', $user->id);
                
            if ($childId) {
                $query->where('child_id', $childId);
            }
            
            $recommendations = $query->latest()->paginate(10);
        } else {
            // Parents see recommendations for their children
            $childIds = $user->children->pluck('id');
            $query = Recommendation::with(['trainer', 'child', 'attachments', 'responses'])
                ->whereIn('child_id', $childIds);
                
            if ($childId) {
                // Ensure the child belongs to the authenticated user
                if (!in_array($childId, $childIds->toArray())) {
                    abort(403, 'You can only view recommendations for your children.');
                }
                $query->where('child_id', $childId);
            }
            
            $recommendations = $query->latest()->paginate(10);
        }

        // Get the specific child if filtering
        $child = null;
        if ($childId) {
            $child = Child::find($childId);
        }

        return view('frontend.recommendations.index', compact('recommendations', 'child'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can create recommendations.');

        $children = Child::with('user')->get();
        
        return view('frontend.recommendations.create', compact('children'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can create recommendations.');

        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:progress,improvement,achievement,general',
            'priority' => 'required|in:low,medium,high',
            'is_public' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $validated['trainer_id'] = auth()->id();
        $validated['is_public'] = $request->has('is_public');

        $recommendation = Recommendation::create($validated);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('recommendations', $filename, 'public');

                RecommendationAttachment::create([
                    'recommendation_id' => $recommendation->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Send notification to parent
        try {
            $child = Child::find($validated['child_id']);
            $child->user->notify(new NewRecommendationNotification($recommendation));
        } catch (\Exception $e) {
            \Log::warning('Failed to send recommendation notification: ' . $e->getMessage());
            // Continue with the redirect even if notification fails
        }

        return redirect()->route('frontend.recommendations.index')
            ->with('success', 'Recommendation created successfully!');
    }

    public function show(Recommendation $recommendation)
    {
        $user = auth()->user();
        
        // Check if user can view this recommendation
        if ($user->hasRole('Trainer')) {
            abort_if($recommendation->trainer_id !== $user->id, 403, 'You can only view your own recommendations.');
        } else {
            abort_if($recommendation->child->user_id !== $user->id, 403, 'You can only view recommendations for your children.');
        }

        // Load responses with the recommendation
        $recommendation->load(['responses.user', 'attachments']);

        // Mark as read if parent is viewing
        if (!$user->hasRole('Trainer') && !$recommendation->isRead()) {
            $recommendation->markAsRead();
        }

        return view('frontend.recommendations.show', compact('recommendation'));
    }

    public function edit(Recommendation $recommendation)
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can edit recommendations.');
        abort_if($recommendation->trainer_id !== auth()->id(), 403, 'You can only edit your own recommendations.');

        $children = Child::with('user')->get();
        
        return view('frontend.recommendations.edit', compact('recommendation', 'children'));
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can update recommendations.');
        abort_if($recommendation->trainer_id !== auth()->id(), 403, 'You can only update your own recommendations.');

        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:progress,improvement,achievement,general',
            'priority' => 'required|in:low,medium,high',
            'is_public' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $validated['is_public'] = $request->has('is_public');

        $recommendation->update($validated);

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('recommendations', $filename, 'public');

                RecommendationAttachment::create([
                    'recommendation_id' => $recommendation->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('frontend.recommendations.show', $recommendation)
            ->with('success', 'Recommendation updated successfully!');
    }

    public function destroy(Recommendation $recommendation)
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can delete recommendations.');
        abort_if($recommendation->trainer_id !== auth()->id(), 403, 'You can only delete your own recommendations.');

        // Delete attachments
        foreach ($recommendation->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $recommendation->delete();

        return redirect()->route('frontend.recommendations.index')
            ->with('success', 'Recommendation deleted successfully!');
    }

    public function deleteAttachment(RecommendationAttachment $attachment)
    {
        abort_if(!auth()->user()->hasRole('Trainer'), 403, 'Only trainers can delete attachments.');
        abort_if($attachment->recommendation->trainer_id !== auth()->id(), 403, 'You can only delete attachments from your own recommendations.');

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully!');
    }

    public function storeResponse(Request $request, Recommendation $recommendation)
    {
        $user = auth()->user();
        
        // Check if user can respond to this recommendation
        if ($user->hasRole('Trainer')) {
            abort_if($recommendation->trainer_id !== $user->id, 403, 'You can only respond to your own recommendations.');
        } else {
            abort_if($recommendation->child->user_id !== $user->id, 403, 'You can only respond to recommendations for your children.');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:10',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = $user->id;
        $validated['recommendation_id'] = $recommendation->id;
        $validated['is_public'] = $request->has('is_public');

        $response = $recommendation->responses()->create($validated);

        // Send notification to the other party
        try {
            if ($user->hasRole('Trainer')) {
                // Trainer responded, notify parent
                $recommendation->child->user->notify(new \App\Notifications\RecommendationResponseNotification($response));
            } else {
                // Parent responded, notify trainer
                $recommendation->trainer->notify(new \App\Notifications\RecommendationResponseNotification($response));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send response notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Response posted successfully!');
    }

    public function deleteResponse(RecommendationResponse $response)
    {
        $user = auth()->user();
        
        // Check if user can delete this response
        if ($user->hasRole('Trainer')) {
            abort_if($response->user_id !== $user->id, 403, 'You can only delete your own responses.');
        } else {
            abort_if($response->user_id !== $user->id, 403, 'You can only delete your own responses.');
        }

        $response->delete();

        return back()->with('success', 'Response deleted successfully!');
    }
}
