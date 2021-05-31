
const Form = () => import('./components/l-limitless-bs4/Form')
const Index = () => import('./components/l-limitless-bs4/Index')
const Show = () => import('./components/l-limitless-bs4/show/Show')

const routes = [
    {
        path: '/contacts',
        components: {
            default: Index,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Contacts',
            metaTags: [
                {
                    name: 'description',
                    content: 'Contacts i.e. customer, supplier, salesperson ...'
                },
                {
                    property: 'og:description',
                    content: 'Contacts i.e. customer, supplier, salesperson ...'
                }
            ]
        },
        alias: [
            '/workshop/contacts'
        ]
    },
    {
        path: '/contacts/create',
        components: {
            default: Form,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Contacts :: Create',
            metaTags: [
                {
                    name: 'description',
                    content: 'Create a contact i.e. customer, supplier, salesperson ...'
                },
                {
                    property: 'og:description',
                    content: 'Create a contact i.e. customer, supplier, salesperson ...'
                }
            ]
        },
        alias: [
            '/workshop/contacts/create'
        ]
    },
    {
        path: '/contacts/:id',
        components: {
            default: Show,
        },
        meta: {
            title: 'Contacts :: Show',
            metaTags: [
                {
                    name: 'description',
                    content: 'Show contact (customer, supplier, salesperson ...)'
                },
                {
                    property: 'og:description',
                    content: 'Show contact (customer, supplier, salesperson ...)'
                }
            ]
        },
        alias: [
            '/workshop/contacts/:id'
        ]
    },
    {
        path: '/contacts/:id/edit',
        components: {
            default: Form,
        },
        meta: {
            title: 'Contacts :: Edit',
            metaTags: [
                {
                    name: 'description',
                    content: 'Edit contact (customer, supplier, salesperson ...)'
                },
                {
                    property: 'og:description',
                    content: 'Edit contact (customer, supplier, salesperson ...)'
                }
            ]
        },
        alias: [
            '/workshop/contacts/:id/edit'
        ]
    },
]

export default routes
