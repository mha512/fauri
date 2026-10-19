<?php

namespace App\Livewire;

use App\Models\Business;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $selectedCustomerId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function selectCustomer(int $id): void
    {
        $this->selectedCustomerId = $this->selectedCustomerId === $id ? null : $id;
    }

    public function render()
    {
        $business = auth()->user()->currentBusiness();

        $customers = Customer::where('business_id', $business->id)
            ->withCount([
                'messages as total_messages',
                'messages as orders_count' => fn($q) => $q->where('category', 'order'),
                'messages as complaints_count' => fn($q) => $q->where('category', 'complaint'),
                'messages as refunds_count' => fn($q) => $q->where('category', 'refund'),
            ])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sq) use ($term) {
                    $sq->where('name', 'like', $term)
                       ->orWhere('phone', 'like', $term)
                       ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        $selectedCustomer = null;
        if ($this->selectedCustomerId) {
            $selectedCustomer = Customer::where('business_id', $business->id)
                ->with(['messages' => fn($q) => $q->orderBy('created_at', 'desc')])
                ->find($this->selectedCustomerId);
        }

        return view('livewire.customers', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'business' => $business,
        ])->layout('layouts.app');
    }
}
