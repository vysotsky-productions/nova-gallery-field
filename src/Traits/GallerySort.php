<?php


namespace VysotskyProductions\NovaGalleryField\Traits;


trait GallerySort
{
    protected $gallerySortable = true;
    protected $sortableColumn;

    public function setSortableDefaults($config)
    {
        $this->sortableColumn = $config['column'];
        $this->gallerySortable = $config['sortable'];
        return $this;
    }

    public function setSortable(string $sortableColumn = 'order', bool $sortable = true)
    {
        $this->sortableColumn = $sortableColumn;
        $this->gallerySortable = $sortable;
        return $this;
    }

    public function getSortableFields()
    {
        return [
            'sortable' => $this->gallerySortable,
            'sortableColumn' => $this->sortableColumn,
        ];
    }
}