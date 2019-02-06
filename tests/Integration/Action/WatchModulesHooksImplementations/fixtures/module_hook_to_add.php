
function implement_hook_page_top(&$page_top): void
{
    $page_top[] = array(
        '#markup' => "I've been trying to stay out",
    );
}
