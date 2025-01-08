import http from 'k6/http';
import { check } from 'k6';

// تحديد إعدادات الاختبار
export const options = {
    vus: 100, // عدد المستخدمين الافتراضيين المتزامنين
    duration: '10s', // مدة تشغيل الاختبار
};

export default function () {
    const url = 'http://127.0.0.1:8000/api/register';

    const payload = {
        name: `testu${__VU}`, // اسم المستخدم يتغير لكل مستخدم
        first_name: 'Test',
        last_name: 'User',
        email: `testu${__VU}@example.com`, // بريد إلكتروني فريد لكل مستخدم
        password: 'password123',
        role_id: '2',
    };

    const params = {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
    };

    const res = http.post(url, payload, params);

    // تسجيل استجابة الخادم
    console.log('Response:', res.body);

    // التحقق من الاستجابة
    check(res, {
        'is status 200': (r) => r.status === 200,
        'response contains user': (r) => r.json('data.user') !== undefined,
        'response contains access token': (r) => r.json('data.access_token') !== undefined,
    });
}
