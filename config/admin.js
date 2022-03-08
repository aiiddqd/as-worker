module.exports = ({ env }) => ({
  auth: {
    secret: env('ADMIN_JWT_SECRET', 'e8795c160c777037181eebf2ab672552'),
  },
});
