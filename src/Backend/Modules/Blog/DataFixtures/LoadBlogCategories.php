<?php

namespace Backend\Modules\Blog\DataFixtures;

class LoadBlogCategories
{
    /**
     * @param \SpoonDatabase $database
     */
    public function load(\SpoonDatabase $database)
    {
        $metaId = $database->insert(
            'meta',
            [
                'keywords' => 'BlogCategory for tests',
                'description' => 'BlogCategory for tests',
                'title' => 'BlogCategory for tests',
                'url' => 'blogcategory-for-tests',
            ]
        );

        $database->insert(
            'blog_categories',
            [
                'meta_id' => $metaId,
                'language' => 'en',
                'title' => 'BlogCategory for tests',
            ]
        );
    }
}
